<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class CurrencyConverter
{
    private const CACHE_KEY = 'exchange_rate:USD:GBP';

    /**
     * Convert a USD amount to GBP.
     */
    public function usdToGbp(float|int|string|null $usd, int $precision = 2): ?float
    {
        if ($usd === null || $usd === '') {
            return null;
        }

        $usd = (float) $usd;

        return round($usd * $this->usdGbpRate(), $precision);
    }

    /**
     * Get the cached USD -> GBP exchange rate.
     *
     * When the Redis key expires, the callback runs again and refetches it.
     */
    public function usdGbpRate(): float
    {
        return Cache::store('redis')->remember(
            self::CACHE_KEY,
            now()->addDay(),
            function () {
                $response = Http::timeout(5)
                    ->retry(2, 250)
                    ->get('https://api.frankfurter.dev/v2/rates', [
                        'base' => 'USD',
                        'quotes' => 'GBP',
                    ])
                    ->throw();

                $data = $response->json();

                $rate = $data[0]['rate'] ?? null;

                if (! is_numeric($rate)) {
                    throw new RuntimeException('Could not fetch USD to GBP exchange rate.');
                }

                return (float) $rate;
            }
        );
    }
}
