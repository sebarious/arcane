<?php

namespace App\Console\Commands;

use App\Models\CardInventory;
use App\Services\PulseApi\PulseApiCardMapper;
use App\Services\PulseApi\PulseApiClient;
use Illuminate\Console\Command;
use Illuminate\Support\Arr;

class RefreshInventoryPricesCommand extends Command
{
    protected $signature = 'arcane:refresh-prices';

    protected $description = 'Refresh market prices for in-stock inventory whose price has aged past the PulseAPI TTL';

    public function handle(PulseApiClient $client): int
    {
        $ttlDays = (int) config('services.pulseapi.price_ttl_days', 5);

        // Keyed off OUR sync time, not PulseAPI's own market_value_updated_at timestamp
        // (see PulseApiCardMapper::toInventoryAttributes) — this TTL governs how often
        // we call PulseAPI, so it has to measure our own sync recency.
        $stale = CardInventory::query()
            ->inStock()
            ->whereNotNull('product_id')
            ->where(function ($q) use ($ttlDays) {
                $q->whereNull('synced_at')
                  ->orWhere('synced_at', '<', now()->subDays($ttlDays));
            })
            ->get();

        if ($stale->isEmpty()) {
            $this->info('Nothing to refresh — all in-stock prices are within the TTL.');
            return self::SUCCESS;
        }

        $productIds = $stale->pluck('product_id')->unique()->values()->all();
        $this->info(sprintf('Refreshing %d distinct card(s) across %d inventory row(s)…', count($productIds), $stale->count()));

        $cards = $client->batchGetCards($productIds);

        $updated = 0;
        foreach ($stale->groupBy('product_id') as $productId => $rows) {
            $card = $cards[$productId] ?? null;
            if (! $card) continue;

            $attributes = Arr::only(
                PulseApiCardMapper::toInventoryAttributes($card),
                ['market_value_pence', 'market_value_updated_at', 'rarity_band', 'synced_at'],
            );

            CardInventory::whereIn('id', $rows->pluck('id'))->update($attributes);
            $updated += $rows->count();
        }

        $this->info("Done. {$updated} row(s) refreshed.");

        return self::SUCCESS;
    }
}
