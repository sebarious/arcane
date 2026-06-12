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
    /** @var BatchType $type */
    $type = $batch->type;
    if (! $type instanceof BatchType) {
      throw new \RuntimeException("Batch {$batch->id} has no valid type.");
    }
    /** @var Game $game */
    $game = $batch->game;
    $packCount       = BatchDesign::packCount($game, $type);
    $targetSale      = BatchDesign::targetSalePrice($game, $type);
    $targetCost      = BatchDesign::targetCost($game, $type);
    $targetMarginPct = (float) config('batches.target_markup_on_cost', 0.30);
    $bandDistribution = \App\Services\Banding\Distribution::forGameAndType($game, $type);
    if (empty($bandDistribution)) {
      throw new \RuntimeException("No band distribution configured for {$game->value}/{$type->value}.");
    }
    // Pull only inventory for that game
    $pool = CardInventory::query()
      ->inStock()
      ->where('game', $game->value)
      ->whereNotNull('rarity_band')
      ->whereNull('pack_id')
      ->get();
    /** @var array<string,\Illuminate\Support\Collection<int,CardInventory>> $bucketed */
    $bucketed = $pool->groupBy('rarity_band');
    // Ensure basic stock availability per band
    foreach ($bandDistribution as $band => $needed) {
      $available = ($bucketed[$band] ?? collect())->count();
      if ($available < $needed) {
        throw new \RuntimeException("Not enough {$band} stock: need {$needed}, have {$available}.");
      }
    }
    // 2) Multi-attempt selection, keep the candidate with margin% closest to target
    $attempts           = 50;
    $minAcceptablePct   = 0.15; // 15% lower bound
    $maxAcceptablePct   = 0.50; // 50% upper bound
    $best               = null;
    for ($i = 0; $i < $attempts; $i++) {
      $selected = collect();
      // Greedy random selection per band
      foreach ($bandDistribution as $band => $needed) {
        $selected = $selected->merge(
          ($bucketed[$band] ?? collect())->shuffle()->take($needed)
        );
      }
      if ($selected->count() !== $packCount) {
        // if we misconfigured band counts vs packCount, bail early
        continue;
      }
      $totalCost   = $selected->sum('cost_pence');
      $totalMarket = $selected->sum('market_value_pence');
      if ($totalCost <= 0) {
        continue;
      }
      $margin   = $targetSale - $totalCost;
      $marginPct = $margin / $totalCost; // markup on cost
      // Reject clearly bad candidates
      if ($marginPct < $minAcceptablePct || $marginPct > $maxAcceptablePct) {
        continue;
      }
      // Keep the candidate closest to our target margin %
      if (! $best || abs($marginPct - $targetMarginPct) < abs($best['margin_pct'] - $targetMarginPct)) {
        $best = [
          'selected'     => $selected,
          'total_cost'   => $totalCost,
          'total_market' => $totalMarket,
          'margin'       => $margin,
          'margin_pct'   => $marginPct,
        ];
      }
    }
    if (! $best) {
      throw new \RuntimeException(
        "Could not find a batch with acceptable margin. " .
          "Review pricing, band distribution, or inventory costs."
      );
    }
    $selected    = $best['selected'];
    $totalCost   = $best['total_cost'];
    $totalMarket = $best['total_market'];
    $margin      = $best['margin'];
    $vatOnMargin = Money::marginSchemeVat($margin);
    // 3) Persist everything in a transaction
    DB::transaction(function () use ($batch, $selected, $totalCost, $totalMarket, $targetSale, $margin, $vatOnMargin) {
      // Create packs 1..N
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
      // Assign one card per pack
      $cards = $selected->values(); // reindex 0..N-1
      foreach ($packs as $index => $pack) {
        /** @var CardInventory $card */
        $card = $cards[$index];
        $card->update([
          'pack_id'                    => $pack->id,
          'status'                     => 'allocated',
          'qr_token'                   => CardInventory::generateQrToken(),
          'allocated_sale_price_pence' => (int) floor($targetSale / $batch->pack_count),
          'margin_pence'               => null, // we set per-card margin below
        ]);
      }
      // Distribute per-card margin (optional, but keeps per-item accounting neat)
      $perCardMargin = (int) floor($margin / max(1, $cards->count()));
      CardInventory::whereIn('id', $cards->pluck('id'))
        ->update(['margin_pence' => $perCardMargin]);
      $batch->update([
        'status'                   => 'committed',
        'total_cost_pence'         => $totalCost,
        'total_market_value_pence' => $totalMarket,
        'sale_price_pence'         => $targetSale,
        'margin_pence'             => $margin,
        'margin_scheme_vat_pence'  => $vatOnMargin,
        'committed_at'             => now(),
      ]);
      // Create invoice (if you wired this here)
      $invoice = Invoice::create([
        'number'                    => Invoice::nextNumber(),
        'store_id'                  => $batch->store_id,
        'batch_id'                  => $batch->id,
        'total_pence'               => $targetSale,
        'internal_cost_pence'       => $totalCost,
        'internal_margin_pence'     => $margin,
        'internal_margin_vat_pence' => $vatOnMargin,
        'status'                    => 'sent',
        'issued_on'                 => now()->toDateString(),
        'due_on'                    => now()->addDays(14)->toDateString(),
      ]);
      $batch->update(['invoice_id' => $invoice->id]);
    });
  }
}
