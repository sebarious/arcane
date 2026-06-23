<?php

namespace App\Services\Batches;

use App\Enums\BatchType;
use App\Models\Batch;
use App\Models\CardInventory;
use App\Models\Pack;
use App\Support\Money;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use App\Models\Invoice;
use App\Enums\Game;

class BatchGenerator
{
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

      // Pool for this game
      $pool = CardInventory::query()
        ->inStock()
        ->where('game', $game->value)
        ->whereNotNull('rarity_band')
        ->whereNull('pack_id')
        ->get();

      $bucketed = $pool->groupBy('rarity_band');

      foreach ($bandDistribution as $band => $needed) {
        $available = ($bucketed[$band] ?? collect())->count();
        if ($available < $needed) {
          throw new \RuntimeException("Not enough {$band} stock: need {$needed}, have {$available}.");
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
            $dedupedPool->take($needed)
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
          'due_on'                    => now()->addDays(14)->toDateString(),
        ]);

        $batch->update(['invoice_id' => $invoice->id]);

        \App\Jobs\GenerateBatchQrSheetJob::dispatch($batch->id)->afterCommit();
      });
    }

  protected function poolWithDuplicateLimit(\Illuminate\Support\Collection $cards, int $limitPerCard): \Illuminate\Support\Collection
  {
    return $cards
      ->groupBy('card_id')
      ->flatMap(function ($group) use ($limitPerCard) {
        return $group->shuffle()->take($limitPerCard);
      })
      ->shuffle()
      ->values();
  }
}
