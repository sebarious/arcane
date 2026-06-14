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
    /** @var Game $game */
    $game = $batch->game;

    if (! $type instanceof BatchType || ! $game instanceof Game) {
      throw new \RuntimeException("Batch {$batch->id} has invalid game or type.");
    }

    $packCount        = BatchDesign::packCount($game, $type);
    $targetSale       = BatchDesign::targetSalePrice($game, $type);
    $targetCost       = BatchDesign::targetCost($game, $type);
    $targetMarket     = BatchDesign::targetMarket($game, $type);
    $targetMarginPct  = (float) config('batches.target_markup_on_cost', 0.30);

    $bandDistribution = \App\Services\Banding\Distribution::forGameAndType($game, $type);
    if (empty($bandDistribution)) {
      throw new \RuntimeException("No band distribution configured for {$game->value}/{$type->value}.");
    }

    // Pull only inventory for that game
    $pool = CardInventory::query()
      ->inStock()
      ->where('game', $game->value)
      ->whereNotNull('rarity_band')
      ->whereNotNull('market_value_pence')
      ->whereNull('pack_id')
      ->get();

    $bucketed = $pool->groupBy('rarity_band');

    // Ensure basic stock availability per band
    foreach ($bandDistribution as $band => $needed) {
      $available = ($bucketed[$band] ?? collect())->count();
      if ($available < $needed) {
        throw new \RuntimeException("Not enough {$band} stock: need {$needed}, have {$available}.");
      }
    }

    // Multi-attempt selection, optimising for both market and margin
    $attempts           = 60;
    $minMarginPct       = 0.15; // 15% min markup on cost
    $maxMarginPct       = 0.50; // 50% max markup on cost
    $minMarketMultiple  = 1.0;  // totalMarket >= sale
    $maxMarketMultiple  = 1.5;  // avoid overpacking too much value
    $best               = null;

    for ($i = 0; $i < $attempts; $i++) {
      $selected = collect();

      foreach ($bandDistribution as $band => $needed) {
        $selected = $selected->merge(
          ($bucketed[$band] ?? collect())->shuffle()->take($needed)
        );
      }

      if ($selected->count() !== $packCount) {
        continue;
      }

      $totalCost   = $selected->sum('cost_pence');
      $totalMarket = $selected->sum('market_value_pence');

      if ($totalCost <= 0 || $totalMarket <= 0) {
        continue;
      }

      $margin         = $targetSale - $totalCost;
      $marginPct      = $margin / $totalCost;
      $marketMultiple = $totalMarket / $targetSale;

      // Reject candidates that are clearly off economically
      if ($marginPct < $minMarginPct || $marginPct > $maxMarginPct) {
        continue;
      }
      if ($marketMultiple < $minMarketMultiple || $marketMultiple > $maxMarketMultiple) {
        continue;
      }

      // Composite score: how close to target margin AND target market?
      $score = abs($marginPct - $targetMarginPct)
        + abs(($totalMarket - $targetMarket) / max(1, $targetMarket));

      if (! $best || $score < $best['score']) {
        $best = [
          'selected'     => $selected,
          'total_cost'   => $totalCost,
          'total_market' => $totalMarket,
          'margin'       => $margin,
          'margin_pct'   => $marginPct,
          'market_mul'   => $marketMultiple,
          'score'        => $score,
        ];
      }
    }

    if (! $best) {
      throw new \RuntimeException(
        "Could not find a batch with acceptable margin/market. " .
          "Review pricing, band distribution, or inventory values."
      );
    }

    $selected    = $best['selected'];
    $totalCost   = $best['total_cost'];
    $totalMarket = $best['total_market'];
    $margin      = $best['margin'];
    $vatOnMargin = Money::marginSchemeVat($margin);

    // Persist everything
    DB::transaction(function () use ($batch, $selected, $totalCost, $totalMarket, $targetSale, $margin, $vatOnMargin) {
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

      \App\Jobs\GenerateBatchQrSheetJob::dispatch($batch->id)->afterCommit();
    });
  }
}
