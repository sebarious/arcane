<?php

use App\Services\CurrencyConverter;

if (! function_exists('usd_to_gbp')) {
  function usd_to_gbp(float|int|string|null $usd, int $precision = 2): ?float
  {
    return app(CurrencyConverter::class)->usdToGbp($usd, $precision);
  }
}