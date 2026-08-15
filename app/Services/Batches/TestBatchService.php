<?php

namespace App\Services\Batches;

use App\Enums\BatchType;
use App\Enums\Game;
use App\Models\Batch;
use App\Models\CardInventory;
use App\Services\Banding\Distribution;
use App\Services\Banding\RarityBander;
use App\Services\Verification\SeededRandom;
use App\Support\Money;
use Illuminate\Support\Facades\Log;

/**
 * Builds the /test-batch public preview — "what does a 500-pack Diamond batch
 * look like" — from real, currently in-stock CardInventory, using the exact
 * same selection logic BatchGenerator uses for a real batch (CandidateSelector::
 * select(), a pure function: arrays in, selected IDs out, no DB writes).
 *
 * Deliberately never runs CardPriceSyncer::syncStale() first (unlike
 * BatchGenerator) — that writes to real CardInventory rows, and this service
 * must never mutate real stock, not even a price refresh. A snapshot's prices
 * can be a little stale; that's an acceptable trade-off given the whole thing
 * gets regenerated weekly anyway (see RefreshTestBatchCommand).
 */
class TestBatchService
{
    public function __construct(protected CandidateSelector $selector) {}

    /** Read-only lookup — bypasses excludeTestBatches, no writes. */
    public function current(): ?Batch
    {
        return Batch::withoutGlobalScope('excludeTestBatches')
            ->where('reference', Batch::TEST_BATCH_REFERENCE)
            ->first();
    }

    /**
     * Finds or creates the singleton demo batch row and regenerates its
     * snapshot from current stock. If the pool can't currently satisfy the
     * Diamond distribution and a previous snapshot already exists, leaves it
     * untouched (logs a warning) rather than blanking the public page — only
     * throws if there's no prior snapshot to fall back to at all.
     *
     * @throws \RuntimeException if selection fails and there's no previous snapshot to keep showing
     */
    public function refresh(): Batch
    {
        $game = Game::Pokemon;
        $type = BatchType::Diamond;

        $batch = Batch::withoutGlobalScope('excludeTestBatches')->firstOrCreate(
            ['reference' => Batch::TEST_BATCH_REFERENCE],
            [
                'store_id' => null,
                'status' => 'demo',
                'type' => $type,
                'game' => $game,
                'pack_count' => BatchDesign::packCount($game, $type),
                'is_test' => true,
                'demo_snapshot' => null,
            ]
        );

        $packCount = BatchDesign::packCount($game, $type);
        $targetSale = BatchDesign::targetSalePrice($game, $type);
        $targetMargin = BatchDesign::targetMargin($game, $type);
        $targetValue = BatchDesign::targetValue($game, $type);

        $bandDistribution = Distribution::forGameAndType($game, $type);
        $tierDistribution = Distribution::tiersForGameAndType($game, $type);
        $duplicateLimits = config('banding.duplicate_limits', []);
        $thresholds = (new RarityBander)->thresholds();

        // Read-only — no syncStale(), see class docblock.
        $pool = CardInventory::available()
            ->where('game', $game->value)
            ->whereNotNull('rarity_band')
            ->orderBy('id')
            ->get();

        $poolData = $pool->map(fn (CardInventory $card) => [
            'id' => $card->id,
            'product_id' => $card->product_id,
            'rarity_band' => $card->rarity_band,
            'market_value_pence' => $card->market_value_pence,
            'cost_pence' => $card->cost_pence,
            'value_pence' => $card->value_pence,
        ])->all();

        $rng = new SeededRandom(bin2hex(random_bytes(16)));

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

        if (! $best) {
            Log::warning('Test batch refresh could not find a valid selection', [
                'debug' => $result['debug'],
            ]);

            if ($batch->demo_snapshot !== null) {
                return $batch;
            }

            $sampleSummary = collect($result['debug']['sample'])
                ->map(fn ($s) => "cost=£{$s['cost']} margin_on_cost=".number_format($s['margin_on_cost'] * 100, 1).'%')
                ->implode(' | ');

            throw new \RuntimeException(sprintf(
                'Could not generate the test batch — not enough live stock currently clears the '.
                'Diamond distribution/margin floor. Tried %d. Rejected: %d below the margin floor, '.
                '%d duplicate-limit failures. Samples: %s',
                $result['debug']['tried'],
                $result['debug']['rejected_lo'],
                $result['debug']['duplicate_failures'],
                $sampleSummary ?: 'none',
            ));
        }

        $cardsById = CardInventory::whereIn('id', $best['selected_ids'])->get()->keyBy('id');

        $cards = collect($best['selected_ids'])->map(function (int $id) use ($cardsById) {
            /** @var CardInventory $card */
            $card = $cardsById[$id];

            return [
                'card_inventory_id' => $card->id,
                'name' => $card->card_name,
                'set' => $card->set_name,
                'number' => $card->card_number,
                'image' => $card->image_url,
                'band' => $card->rarity_band,
                'market_value_pence' => $card->market_value_pence,
                'product_badges' => $card->product_badges,
            ];
        })->values()->all();

        $marginAtCost = $targetSale - $best['total_cost'];

        $batch->update([
            'demo_snapshot' => [
                'band_counts' => $bandDistribution,
                'cards' => $cards,
            ],
            'pack_count' => $packCount,
            'total_cost_pence' => $best['total_cost'],
            'total_market_value_pence' => $best['total_market'],
            'sale_price_pence' => $targetSale,
            'margin_pence' => $marginAtCost,
            'margin_scheme_vat_pence' => Money::marginSchemeVat($marginAtCost),
        ]);

        return $batch;
    }
}
