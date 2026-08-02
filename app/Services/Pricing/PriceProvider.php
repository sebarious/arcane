<?php

namespace App\Services\Pricing;

use App\Models\CardInventory;

interface PriceProvider
{
  public function refreshPrice(CardInventory $item, string $condition = 'NM'): void;
}
