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

    $card = $this->client->getCard($item->product_id);
    if (! $card) return;

    $attributes = PulseApiCardMapper::toInventoryAttributes($card);

    $item->update(Arr::only($attributes, [
      'market_value_pence', 'market_value_updated_at', 'rarity_band',
      'image_url', 'rarity', 'rarity_rank', 'synced_at',
    ]));
  }
}
