<?php

use App\Models\CardInventory;
use App\Services\CurrencyConverter;

if (! function_exists('usd_to_gbp')) {
  function usd_to_gbp(float|int|string|null $usd, int $precision = 2): ?float
  {
    return app(CurrencyConverter::class)->usdToGbp($usd, $precision);
  }
}

if (! function_exists('whats_in_the_pool')) {
  /**
   * The homepage "Live Pool" feed. Every currently-unallocated in-stock
   * card, highest value first — the exact same pool RipDrawer draws
   * Digital Rips from, and Batch generation draws physical packs from, so
   * this is a genuine live view of what a buyer could actually pull right
   * now (not just cards already sealed into a physical pack, as before).
   */
  function whats_in_the_pool(): array
  {
    return CardInventory::query()
      ->where('status', 'in_stock')
      ->whereNotIn('rarity_band', ['common', 'rare'])
      ->orderByDesc('market_value_pence')
      ->take(10)
      ->get()
      ->map(fn (CardInventory $inv) => [
        'id' => $inv->id,
        'card' => [
          'name'   => $inv->card_name,
          'set'    => $inv->set_name,
          'number' => $inv->card_number,
          'image'  => $inv->image_url,
          'band'   => $inv->rarity_band,
        ],
      ])
      ->values()
      ->toArray();
  }
}