<?php

namespace App\Services\Batches;

use App\Enums\BatchType;
use App\Models\Batch;
use App\Models\CardInventory;
use App\Models\Pack;
use App\Services\Banding\RarityBander;
use App\Services\PulseApi\CardPriceSyncer;
use App\Services\Stores\StoreCreditService;
use App\Support\Money;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use App\Models\Invoice;
use App\Enums\Game;

class BatchGenerator
{
  public function __construct(
    protected StoreCreditService $creditService,
    protected CardPriceSyncer $priceSyncer,
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

    $attempts = 150;
    $best     = null;
    $debug = [
      'tried'               => 0,
      'rejected_lo'         => 0,
      'rejected_hi'         => 0,
      'duplicate_failures'  => 0,
      'sample'              => [],
    ];


    /** @var BatchType $type */
      $type = $batch->type;
      /** @var Game $game */
      $game = $batch->game;

      if (! $type instanceof BatchType || ! $game instanceof Game) {
        throw new \RuntimeException("Batch {$batch->id} has invalid game or type.");
      }

      $packCount       = BatchDesign::packCount($game, $type);
      $targetSale      = BatchDesign::targetSalePrice($game, $type);
      $targetMargin    = BatchDesign::targetMargin($game, $type);   // 0.40 / 0.30 / 0.20
      $targetValue     = BatchDesign::targetValue($game, $type);    // sale / (1 + margin)

      $bandDistribution = \App\Services\Banding\Distribution::forGameAndType($game, $type);
      if (empty($bandDistribution)) {
        throw new \RuntimeException("No band distribution configured for {$game->value}/{$type->value}.");
      }

      // Refresh any stale prices in this pool before selecting from it — a card
      // priced weeks ago could since have moved bands entirely, so this needs to
      // happen before we group by rarity_band below, not after.
      $this->priceSyncer->syncStale(
        CardInventory::query()
          ->inStock()
          ->where('game', $game->value)
          ->whereNull('pack_id')
      );

      // Pool for this game
      $pool = CardInventory::query()
        ->inStock()
        ->where('game', $game->value)
        ->whereNotNull('rarity_band')
        ->whereNull('pack_id')
        ->get();

      $bucketed = $pool->groupBy('rarity_band');
      $duplicateLimits = config('banding.duplicate_limits', []);

      foreach ($bandDistribution as $band => $needed) {
        $bandPool  = $bucketed[$band] ?? collect();
        $available = $bandPool->count();
        if ($available < $needed) {
          throw new \RuntimeException("Not enough {$band} stock: need {$needed}, have {$available}.");
        }

        // Raw count alone isn't enough — duplicate copies of the same card are capped
        // per band (config('banding.duplicate_limits')), so also check what's actually
        // reachable once that cap is applied, to fail fast with a clear reason instead
        // of burning all 150 selection attempts on a band that can never be satisfied.
        $limitPerCard = (int) ($duplicateLimits[$band] ?? 1);
        $capped       = $this->maxAvailableWithDuplicateLimit($bandPool, $limitPerCard);
        if ($capped < $needed) {
          throw new \RuntimeException(
            "Not enough distinct {$band} stock: need {$needed}, but the duplicate limit ".
            "({$limitPerCard} per card) caps available stock at {$capped} (from {$available} raw)."
          );
        }
      }

      // Acceptable margin windows around the target (e.g. ±10pp)
      $minMargin = max(0, $targetMargin - 0.10);
      $maxMargin = $targetMargin + 0.10;

      $attempts = 150;
      $best     = null;

      for ($i = 0; $i < $attempts; $i++) {
        $selected = collect();
        $duplicateLimits = config('banding.duplicate_limits', []);
        foreach ($bandDistribution as $band => $needed) {
          $bandPool = $bucketed[$band] ?? collect();
          $limitPerCard = (int) ($duplicateLimits[$band] ?? 1);
          $dedupedPool = $this->poolWithDuplicateLimit($bandPool, $limitPerCard);
          // If duplicate limits make it impossible to satisfy the band, skip this attempt
          if ($dedupedPool->count() < $needed) {
            $debug['duplicate_failures']++;
            continue 2;
          }
          $selected = $selected->merge(
            $this->selectForBand($dedupedPool, $needed, $band)
          );
        }
        if ($selected->count() !== $packCount) continue;
        $totalValue  = $selected->sum->value_pence;
        $totalCost   = $selected->sum('cost_pence');
        $totalMarket = $selected->sum('market_value_pence');
        if ($totalValue <= 0) continue;
        $marginVsValue = ($targetSale - $totalValue) / $totalValue;
        $debug['tried']++;
        if ($i < 5) {
          $debug['sample'][] = [
            'value'   => round($totalValue / 100, 2),
            'cost'    => round($totalCost / 100, 2),
            'market'  => round($totalMarket / 100, 2),
            'margin'  => round($marginVsValue, 4),
          ];
        }
        if ($marginVsValue < $minMargin) {
          $debug['rejected_lo']++;
          continue;
        }
        if ($marginVsValue > $maxMargin) {
          $debug['rejected_hi']++;
          continue;
        }
        $score = abs($marginVsValue - $targetMargin)
          + abs(($totalValue - $targetValue) / max(1, $targetValue));
        if (! $best || $score < $best['score']) {
          $best = [
            'selected'      => $selected,
            'total_value'   => $totalValue,
            'total_cost'    => $totalCost,
            'total_market'  => $totalMarket,
            'margin_value'  => $marginVsValue,
            'score'         => $score,
          ];
        }
      }
      if (! $best) {
        $sampleSummary = collect($debug['sample'])
          ->map(fn($s) => "value=£{$s['value']} margin=" . number_format($s['margin'] * 100, 1) . '%')
          ->implode(' | ');
        throw new \RuntimeException(sprintf(
          'Could not find a batch within margin window for %s/%s. ' .
            'Target sale=£%.2f, target value=£%.2f, target margin=%.1f%% (window %.1f%% – %.1f%%). ' .
            'Tried %d. Rejected: %d too-low, %d too-high, %d duplicate-limit failures. Samples: %s',
          $game->value,
          $type->value,
          $targetSale / 100,
          $targetValue / 100,
          $targetMargin * 100,
          $minMargin * 100,
          $maxMargin * 100,
          $debug['tried'],
          $debug['rejected_lo'],
          $debug['rejected_hi'],
          $debug['duplicate_failures'],
          $sampleSummary ?: 'none',
        ));
      }

      $selected     = $best['selected'];
      $totalCost    = $best['total_cost'];      // what we actually paid
      $totalMarket  = $best['total_market'];    // market only
      $totalValue   = $best['total_value'];     // market w/ cost fallback (used for selection only)
      $marginAtCost = $targetSale - $totalCost; // what hits the books
      $vatOnMargin  = Money::marginSchemeVat($marginAtCost);

      DB::transaction(function () use ($batch, $selected, $totalCost, $totalMarket, $targetSale, $marginAtCost, $vatOnMargin) {
        $packs = collect();
        for ($i = 1; $i <= $batch->pack_count; $i++) {
          $packs->push(
            Pack::create([
              'batch_id'    => $batch->id,
              'sequence_no' => $i,
              'status'      => 'sealed',
            ])
          );
        }

        $cards = $selected->values();

        // Snapshot the two priciest cards now, before allocation/sale can touch
        // them — this is the only point where "top cards in this batch" is ever
        // computed, so the Card Lists storefront thumbnail stays fixed even once
        // these particular cards are pulled and sold out of the batch.
        $topCards = $cards->sortByDesc('market_value_pence')->values();

        foreach ($packs as $index => $pack) {
          /** @var CardInventory $card */
          $card = $cards[$index];
          $card->update([
            'pack_id'                    => $pack->id,
            'status'                     => 'allocated',
            'qr_token'                   => CardInventory::generateQrToken(),
            'allocated_sale_price_pence' => (int) floor($targetSale / $batch->pack_count),
            'margin_pence'               => null,
          ]);
        }

        $perCardMargin = (int) floor($marginAtCost / max(1, $cards->count()));
        CardInventory::whereIn('id', $cards->pluck('id'))
          ->update(['margin_pence' => $perCardMargin]);

        $batch->update([
          'status'                   => 'committed',
          'total_cost_pence'         => $totalCost,         // real cost (for accounting)
          'total_market_value_pence' => $totalMarket,       // real market (for reporting)
          'sale_price_pence'         => $targetSale,
          'margin_pence'             => $marginAtCost,      // sale - cost (real margin)
          'margin_scheme_vat_pence'  => $vatOnMargin,
          'failure_reason'           => null,
          'failed_at'                => null,
          'committed_at'             => now(),
          'top_card_1_id'            => $topCards->get(0)?->id,
          'top_card_2_id'            => $topCards->get(1)?->id,
        ]);

        $invoice = Invoice::create([
          'number'                    => Invoice::nextNumber(),
          'store_id'                  => $batch->store_id,
          'batch_id'                  => $batch->id,
          'total_pence'               => $targetSale,
          'internal_cost_pence'       => $totalCost,
          'internal_margin_pence'     => $marginAtCost,
          'internal_margin_vat_pence' => $vatOnMargin,
          'status'                    => 'sent',
          'issued_on'                 => now()->toDateString(),
          'due_on'                    => now()->addHours(48)->toDateString(),
        ]);

        $batch->update(['invoice_id' => $invoice->id]);

        // Auto-offset the invoice with any credit this store already has in its
        // wallet (e.g. from appraised affiliate sell submissions), up to the
        // invoice's value — leftover credit, if any, carries forward untouched.
        $this->creditService->deductForInvoice($batch->store, $invoice);

        \App\Jobs\GenerateBatchQrSheetJob::dispatch($batch->id)->afterCommit();
        \App\Jobs\SendInvoiceEmailJob::dispatch($invoice->id)->afterCommit();
      });
    }

  protected function poolWithDuplicateLimit(\Illuminate\Support\Collection $cards, int $limitPerCard): \Illuminate\Support\Collection
  {
    return $cards
      ->groupBy('product_id')
      ->flatMap(function ($group) use ($limitPerCard) {
        return $group->shuffle()->take($limitPerCard);
      })
      ->shuffle()
      ->values();
  }

  /**
   * How many cards from this pool are actually reachable once the per-product-id
   * duplicate cap is applied — i.e. sum(min(copies, $limitPerCard)) across distinct
   * cards, rather than the pool's raw count. Same shape as poolWithDuplicateLimit()
   * but just the count, so callers can check feasibility without building the pool.
   */
  protected function maxAvailableWithDuplicateLimit(\Illuminate\Support\Collection $cards, int $limitPerCard): int
  {
    return $cards
      ->groupBy('product_id')
      ->sum(fn ($group) => min($group->count(), $limitPerCard));
  }

  /**
   * Pick $needed cards for a band. For every band except mythic (a deliberate chase
   * tier — high variance is the point), split the band's own price range (from
   * RarityBander) into low/mid/high thirds and draw roughly evenly from each, so the
   * selection's average market value gravitates toward the middle of the band rather
   * than wherever a plain random draw happens to land.
   */
  protected function selectForBand(Collection $pool, int $needed, string $band): Collection
  {
    if ($band === 'mythic') {
      return $pool->shuffle()->take($needed);
    }

    $range = (new RarityBander())->thresholds()[$band] ?? null;
    if (! $range) {
      return $pool->shuffle()->take($needed);
    }

    $tierWidth = max(1, ($range['max'] - $range['min']) / 3);
    $lowMax    = $range['min'] + $tierWidth;
    $midMax    = $range['min'] + (2 * $tierWidth);

    $tiers = [
      'low'  => $pool->filter(fn ($c) => $c->market_value_pence < $lowMax),
      'mid'  => $pool->filter(fn ($c) => $c->market_value_pence >= $lowMax && $c->market_value_pence < $midMax),
      'high' => $pool->filter(fn ($c) => $c->market_value_pence >= $midMax),
    ];

    $selected   = collect();
    $selectedIds = [];

    foreach ($this->tierTargets($needed) as $tier => $count) {
      $take = $tiers[$tier]->shuffle()->take($count);
      $selected = $selected->merge($take);
      $selectedIds = [...$selectedIds, ...$take->pluck('id')->all()];
    }

    // A tier came up short (not enough stock at that price point) — top up from
    // whatever's left in the band so we still hit $needed, just less evenly spread.
    if ($selected->count() < $needed) {
      $extra = $pool
        ->reject(fn ($c) => in_array($c->id, $selectedIds, true))
        ->shuffle()
        ->take($needed - $selected->count());
      $selected = $selected->merge($extra);
    }

    return $selected->values();
  }

  /**
   * How many of $needed should come from the low/mid/high thirds of the band's range.
   * Small counts are special-cased to keep the average centred: 1 alone goes to mid
   * (best single approximation of the midpoint); 2 splits low+high (their average
   * approximates the midpoint without needing mid-tier stock).
   *
   * @return array{low: int, mid: int, high: int}
   */
  protected function tierTargets(int $needed): array
  {
    if ($needed <= 0) return ['low' => 0, 'mid' => 0, 'high' => 0];
    if ($needed === 1) return ['low' => 0, 'mid' => 1, 'high' => 0];
    if ($needed === 2) return ['low' => 1, 'mid' => 0, 'high' => 1];

    $base      = intdiv($needed, 3);
    $remainder = $needed % 3;
    $targets   = ['low' => $base, 'mid' => $base, 'high' => $base];

    if ($remainder === 1) {
      $targets['mid']++;
    } elseif ($remainder === 2) {
      $targets['low']++;
      $targets['high']++;
    }

    return $targets;
  }
}
