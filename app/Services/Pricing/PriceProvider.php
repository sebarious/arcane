<?php

namespace App\Services\Pricing;

use App\Models\Card;

interface PriceProvider
{
  public function refreshPrice(Card $card, string $condition = 'NM'): void;
}
