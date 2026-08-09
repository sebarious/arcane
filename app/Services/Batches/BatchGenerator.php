<?php

namespace App\Services\Batches;

use App\Enums\BatchType;
use App\Enums\Game;
use App\Jobs\GenerateBatchQrSheetJob;
use App\Jobs\SendInvoiceEmailJob;
use App\Models\Batch;
use App\Models\CardInventory;
use App\Models\Invoice;
use App\Models\Pack;
use App\Services\Banding\Distribution;
use App\Services\Banding\RarityBander;
use App\Services\PulseApi\CardPriceSyncer;
use App\Services\Stores\StoreCreditService;
use App\Services\Verification\SeededRandom;
use App\Support\Money;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class BatchGenerator
{
    public function __construct(
        protected StoreCreditService $creditService,
        protected CardPriceSyncer $priceSyncer,
        protected CandidateSelector $selector,
    ) {}

    /**
     * Generate cards & packs for a batch.
     *
     * @throws \RuntimeException if not enough stock or constraints can’t be met.
     */
    public function generate(Batch $batch): void
    {
        if ($batch->status !== 'draft') {
            throw new \RuntimeException("Batch {$batch->id} is not in draft status.");
        }

        if (! $batch->verification_seed) {
            throw new \RuntimeException("Batch {$batch->id} has no verification seed — every batch should get one on creation.");
        }

        /** @var BatchType $type */
        $type = $batch->type;
        /** @var Game $game */
        $game = $batch->game;

        if (! $type instanceof BatchType || ! $game instanceof Game) {
            throw new \RuntimeException("Batch {$batch->id} has invalid game or type.");
        }

        $packCount = BatchDesign::packCount($game, $type);
        $targetSale = BatchDesign::targetSalePrice($game, $type);
        $targetMargin = BatchDesign::targetMargin($game, $type);   // profit target against COST — 0.25 / 0.20 / 0.15
        $targetValue = BatchDesign::targetValue($game, $type);    // sizing only — sale * target_market_multiple

        $bandDistribution = Distribution::forGameAndType($game, $type);
        if (empty($bandDistribution)) {
            throw new \RuntimeException("No band distribution configured for {$game->value}/{$type->value}.");
        }

        // Per-band split across each band's 3 price tiers — see CandidateSelector::
        // selectForBand(). A band absent here (or whose tier counts don't sum to its
        // total above) just falls back to an even auto-split, so this is optional.
        $tierDistribution = Distribution::tiersForGameAndType($game, $type);

        // Refresh any stale prices in this pool before selecting from it — a card
        // priced weeks ago could since have moved bands entirely, so this needs to
        // happen before we group by rarity_band below, not after.
        $this->priceSyncer->syncStale(
            CardInventory::query()
                ->inStock()
                ->where('game', $game->value)
                ->whereNull('pack_id')
        );

        // Pool for this game. Explicitly ordered — this feeds the snapshot that a
        // later verification replays against, so it needs a stable, reproducible
        // order rather than whatever an unordered scan happens to return.
        $pool = CardInventory::query()
            ->inStock()
            ->where('game', $game->value)
            ->whereNotNull('rarity_band')
            ->whereNull('pack_id')
            ->orderBy('id')
            ->get();

        $bucketed = $pool->groupBy('rarity_band');
        $duplicateLimits = config('banding.duplicate_limits', []);

        foreach ($bandDistribution as $band => $needed) {
            $bandPool = $bucketed[$band] ?? collect();
            $available = $bandPool->count();
            if ($available < $needed) {
                throw new \RuntimeException("Not enough {$band} stock: need {$needed}, have {$available}.");
            }

            // Raw count alone isn't enough — duplicate copies of the same card are capped
            // per band (config('banding.duplicate_limits')), so also check what's actually
            // reachable once that cap is applied, to fail fast with a clear reason instead
            // of burning all 150 selection attempts on a band that can never be satisfied.
            $limitPerCard = (int) ($duplicateLimits[$band] ?? 1);
            $capped = $this->maxAvailableWithDuplicateLimit($bandPool, $limitPerCard);
            if ($capped < $needed) {
                throw new \RuntimeException(
                    "Not enough distinct {$band} stock: need {$needed}, but the duplicate limit ".
                    "({$limitPerCard} per card) caps available stock at {$capped} (from {$available} raw)."
                );
            }
        }

        $thresholds = (new RarityBander)->thresholds();

        $poolData = $pool->map(fn (CardInventory $card) => [
            'id' => $card->id,
            'product_id' => $card->product_id,
            'rarity_band' => $card->rarity_band,
            'market_value_pence' => $card->market_value_pence,
            'cost_pence' => $card->cost_pence,
            'value_pence' => $card->value_pence,
        ])->all();

        $rng = new SeededRandom($batch->verification_seed);

        $result = $this->selector->select(
            pool: $poolData,
            bandDistribution: $bandDistribution,
            duplicateLimits: $duplicateLimits,
            thresholds: $thresholds,
            targetSale: $targetSale,
            targetMargin: $targetMargin,
            targetValue: $targetValue,
            packCount: $packCount,
            rng: $rng,
            tierDistribution: $tierDistribution,
        );

        $best = $result['best'];
        $debug = $result['debug'];

        if (! $best) {
            $sampleSummary = collect($debug['sample'])
                ->map(fn ($s) => "cost=£{$s['cost']} margin_on_cost=".number_format($s['margin_on_cost'] * 100, 1).'% (value=£'.$s['value'].' margin_on_value='.number_format($s['margin_on_value'] * 100, 1).'%)')
                ->implode(' | ');
            throw new \RuntimeException(sprintf(
                'Could not find a batch clearing the profit-on-cost floor for %s/%s. '.
                  'Target sale=£%.2f, target value=£%.2f (sizing only), minimum margin on cost=%.1f%% (no upper bound — more is fine). '.
                  'Tried %d. Rejected: %d below the margin floor, %d duplicate-limit failures. Samples: %s',
                $game->value,
                $type->value,
                $targetSale / 100,
                $targetValue / 100,
                $targetMargin * 100,
                $debug['tried'],
                $debug['rejected_lo'],
                $debug['duplicate_failures'],
                $sampleSummary ?: 'none',
            ));
        }

        $totalCost = $best['total_cost'];      // what we actually paid
        $totalMarket = $best['total_market'];    // market only
        $marginAtCost = $targetSale - $totalCost; // what hits the books
        $vatOnMargin = Money::marginSchemeVat($marginAtCost);

        $snapshotPath = $this->writeVerificationSnapshot($batch, $poolData, $bandDistribution, $tierDistribution, $duplicateLimits, $thresholds, $targetSale, $targetMargin, $targetValue, $packCount, $best['selected_ids']);

        // Cards in final pack order — whereIn() doesn't preserve the IN-list order,
        // so re-sort by the selector's own selected_ids sequence explicitly. That
        // order is what determines which pack (by sequence_no) gets which card, so
        // getting this wrong would silently break both the batch itself and any
        // later replay of it.
        $cardsById = CardInventory::whereIn('id', $best['selected_ids'])->get()->keyBy('id');
        $cards = collect($best['selected_ids'])->map(fn ($id) => $cardsById[$id]);

        DB::transaction(function () use ($batch, $cards, $totalCost, $totalMarket, $targetSale, $marginAtCost, $vatOnMargin, $snapshotPath) {
            $packs = collect();
            for ($i = 1; $i <= $batch->pack_count; $i++) {
                $packs->push(
                    Pack::create([
                        'batch_id' => $batch->id,
                        'sequence_no' => $i,
                        'status' => 'sealed',
                    ])
                );
            }

            // Snapshot the two priciest cards now, before allocation/sale can touch
            // them — this is the only point where "top cards in this batch" is ever
            // computed, so the Card Lists storefront thumbnail stays fixed even once
            // these particular cards are pulled and sold out of the batch.
            $topCards = $cards->sortByDesc('market_value_pence')->values();

            foreach ($packs as $index => $pack) {
                /** @var CardInventory $card */
                $card = $cards[$index];
                $card->update([
                    'pack_id' => $pack->id,
                    'status' => 'allocated',
                    'qr_token' => CardInventory::generateQrToken(),
                    'allocated_sale_price_pence' => (int) floor($targetSale / $batch->pack_count),
                    'margin_pence' => null,
                ]);
            }

            $perCardMargin = (int) floor($marginAtCost / max(1, $cards->count()));
            CardInventory::whereIn('id', $cards->pluck('id'))
                ->update(['margin_pence' => $perCardMargin]);

            // Lands as 'pending_review', not 'committed' — cards are picked and
            // allocated, but nothing customer/seller-facing happens yet (no
            // invoice, no email, no storefront visibility). Three explicit
            // steps take it live from here — see sendInvoice() and publish()
            // below — so it can be checked over first (the QR sheet and
            // verification page both already work at this stage, see
            // BatchResource::qrSheetAction/verifyAction) and the seller isn't
            // billed, let alone live, until a human's actually looked at it.
            $batch->update([
                'status' => 'pending_review',
                'total_cost_pence' => $totalCost,         // real cost (for accounting)
                'total_market_value_pence' => $totalMarket,       // real market (for reporting)
                'sale_price_pence' => $targetSale,
                'margin_pence' => $marginAtCost,      // sale - cost (real margin)
                'margin_scheme_vat_pence' => $vatOnMargin,
                'failure_reason' => null,
                'failed_at' => null,
                'top_card_1_id' => $topCards->get(0)?->id,
                'top_card_2_id' => $topCards->get(1)?->id,
                'verification_revealed_at' => now(),
                'verification_snapshot_path' => $snapshotPath,
            ]);
        });
    }

    /**
     * Second step: generates the invoice, offsets any existing store credit
     * against it, emails it to the seller, and dispatches the QR sheet job —
     * physical pack labels don't depend on payment, so there's no reason to
     * hold them back until publish() while the seller's still paying. Doesn't
     * touch the storefront. Moves the batch to 'awaiting_payment'; publish()
     * is the explicit third step once it's actually been paid.
     */
    public function sendInvoice(Batch $batch): void
    {
        if ($batch->status !== 'pending_review') {
            throw new \RuntimeException("Batch {$batch->id} is not pending review.");
        }

        DB::transaction(function () use ($batch) {
            $invoice = Invoice::create([
                'number' => Invoice::nextNumber(),
                'store_id' => $batch->store_id,
                'batch_id' => $batch->id,
                'total_pence' => $batch->sale_price_pence,
                'internal_cost_pence' => $batch->total_cost_pence,
                'internal_margin_pence' => $batch->margin_pence,
                'internal_margin_vat_pence' => $batch->margin_scheme_vat_pence,
                'status' => 'sent',
                'issued_on' => now()->toDateString(),
                'due_on' => now()->addHours(48)->toDateString(),
            ]);

            $batch->update([
                'invoice_id' => $invoice->id,
                'status' => 'awaiting_payment',
            ]);

            // Auto-offset the invoice with any credit this store already has in its
            // wallet (e.g. from appraised affiliate sell submissions), up to the
            // invoice's value — leftover credit, if any, carries forward untouched.
            $this->creditService->deductForInvoice($batch->store, $invoice);

            SendInvoiceEmailJob::dispatch($invoice->id)->afterCommit();
            GenerateBatchQrSheetJob::dispatch($batch->id)->afterCommit();
        });
    }

    /**
     * Third and final step: marks the batch 'committed' — public on the
     * storefront. Can be published ahead of the invoice being marked paid.
     */
    public function publish(Batch $batch): void
    {
        if ($batch->status !== 'awaiting_payment') {
            throw new \RuntimeException("Batch {$batch->id} is not awaiting payment.");
        }

        $batch->update([
            'status' => 'committed',
            'committed_at' => now(),
        ]);
    }

    /**
     * The frozen inputs a later verification needs to replay CandidateSelector::select()
     * and reproduce this exact batch — the stock pool as it existed at generation time,
     * plus every config value that fed into the search. Stored on disk (not the batches
     * row) since the pool alone can run to hundreds/thousands of rows.
     *
     * Stored on the `local` disk (storage/app/private) — genuinely private,
     * nothing currently serves it to the public. cost_pence is kept as-is:
     * CandidateSelector's profitability gate is now judged against cost (see
     * select()), so a redacted cost_pence here would make BatchVerifier's
     * replay compute total_cost=0 for every attempt and reject everything —
     * verification would always fail, not just look different. If a public
     * "download the snapshot" feature is ever built, redact cost_pence at
     * *that* output boundary instead of here, so this stored copy — the one
     * BatchVerifier's replay actually depends on for correctness — stays intact.
     *
     * Also records original_assignment — the winning selection's card IDs, in final
     * pack order, exactly as generated. BatchVerifier checks a replay against *this*,
     * not against the batch's live packs()/CardInventory rows — those can legitimately
     * change later (a batch merge reassigns packs to a different batch entirely), and
     * a verification should reflect what was originally, provably generated, not
     * whatever a later, unrelated reorganization happens to look like today.
     */
    protected function writeVerificationSnapshot(
        Batch $batch,
        array $poolData,
        array $bandDistribution,
        array $tierDistribution,
        array $duplicateLimits,
        array $thresholds,
        int $targetSale,
        float $targetMargin,
        int $targetValue,
        int $packCount,
        array $originalAssignment,
    ): string {
        $path = "verification-snapshots/{$batch->id}.json";

        Storage::disk('local')->put($path, json_encode([
            'pool' => $poolData,
            'band_distribution' => $bandDistribution,
            'tier_distribution' => $tierDistribution,
            'duplicate_limits' => $duplicateLimits,
            'thresholds' => $thresholds,
            'target_sale_pence' => $targetSale,
            'target_margin' => $targetMargin,
            'target_value_pence' => $targetValue,
            'pack_count' => $packCount,
            'attempts' => 150,
            'original_assignment' => $originalAssignment,
        ], JSON_PRETTY_PRINT));

        return $path;
    }

    /**
     * How many cards from this pool are actually reachable once the per-product-id
     * duplicate cap is applied — i.e. sum(min(copies, $limitPerCard)) across distinct
     * cards, rather than the pool's raw count. Used only for the fail-fast feasibility
     * check above; the real (seeded) selection happens in CandidateSelector.
     */
    protected function maxAvailableWithDuplicateLimit(Collection $cards, int $limitPerCard): int
    {
        return $cards
            ->groupBy('product_id')
            ->sum(fn ($group) => min($group->count(), $limitPerCard));
    }
}
