<?php

namespace App\Services\Pricing;

use App\Models\CardInventory;
use App\Services\PulseApi\PulseApiCardMapper;
use App\Services\PulseApi\PulseApiClient;
use Illuminate\Support\Arr;

class PulseApiPriceProvider implements PriceProvider
{
  public function __construct(
    protected PulseApiClient $client,
  ) {}

  // $condition is part of PulseAPI's product_id itself (e.g. a |hp suffix), not a
  // separate query param, so a single fetch already reflects this item's condition.
  public function refreshPrice(CardInventory $item, string $condition = 'NM'): void
  {
    if (! $item->product_id) return;
    if ($item->price_locked) return;

    $card = $this->client->getCard($item->product_id);
    if (! $card) return;

    $attributes = PulseApiCardMapper::toInventoryAttributes($card);

    $fields = ['market_value_pence', 'market_value_updated_at', 'image_url', 'rarity', 'rarity_rank', 'synced_at'];

    // This is the one write path reachable directly from the admin "Resync
    // price" button on any card, regardless of its lifecycle stage — market
    // value still refreshes (useful even for an already-sold card, for
    // reporting), but the band itself is frozen once the card has left the
    // available pool. See CardInventory::isBandLocked().
    if (! $item->isBandLocked()) {
      $fields[] = 'rarity_band';
    }

    $item->update(Arr::only($attributes, $fields));
  }
}
