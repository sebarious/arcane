<?php

use App\Models\Pack;
use App\Services\CurrencyConverter;
use Illuminate\Support\Collection;

if (! function_exists('usd_to_gbp')) {
  function usd_to_gbp(float|int|string|null $usd, int $precision = 2): ?float
  {
    return app(CurrencyConverter::class)->usdToGbp($usd, $precision);
  }
}

if (! function_exists('whats_in_the_pool')) {
  function whats_in_the_pool(): array
  {
    return Pack::query()
      ->where('status', 'sealed')
      ->whereHas('batch', function ($query) {
        $query->whereIn('status', ['committed', 'dispatched']);
      })
      ->whereHas('card')
      ->whereHas('card.card.inventory', function ($q) {
        $q->whereNotIn('rarity_band', ['common', 'rare']);
      })
      ->with([
        'batch.store',
        'card.card',
        'card.card.inventory',
      ])
      ->get()
      ->sortByDesc(
        fn($pack) =>
        $pack->created_at
          ?? $pack->updated_at
      )
      ->take(10)
      ->map(function ($pack) {
        $inv  = $pack->card;
        $card = $inv?->card;

        return [
          'id'       => $pack->id,
          'sequence' => $pack->sequence_no,

          'store' => [
            'id'   => $pack->batch?->store?->id,
            'name' => $pack->batch?->store?->name,
          ],

          'batch' => [
            'id'        => $pack->batch_id,
            'reference' => $pack->batch?->reference,
          ],

          'card' => $card ? [
            'name'   => $card->name,
            'set'    => $card->set_name,
            'number' => $card->card_number,
            'image'  => $card->image_front,
            'band'   => $inv?->rarity_band,
          ] : null,
        ];
      })
      ->values()
      ->toArray() ?? [];
  }
}