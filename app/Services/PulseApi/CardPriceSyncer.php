<?php

namespace App\Services\PulseApi;

use App\Models\CardInventory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Arr;

class CardPriceSyncer
{
    public function __construct(
        protected PulseApiClient $client,
    ) {}

    /**
     * Bulk-refresh market prices (and, since price drift can move a card between bands,
     * rarity_band) for any card matching $scope whose price hasn't been synced within
     * the TTL — used before batch generation so card selection works off current data
     * rather than whatever was true when the card was last intaken/synced.
     *
     * @param  Builder<CardInventory>  $scope  Query already scoped to the relevant pool
     *                                          (game, in-stock, unallocated, etc) — this
     *                                          method only adds the staleness filter.
     * @return int Number of card_inventory rows updated.
     */
    public function syncStale(Builder $scope, ?int $ttlDays = null): int
    {
        $ttlDays = $ttlDays ?? (int) config('services.pulseapi.price_ttl_days', 5);

        $staleProductIds = (clone $scope)
            ->whereNotNull('product_id')
            ->where(function (Builder $query) use ($ttlDays) {
                $query->whereNull('synced_at')
                    ->orWhere('synced_at', '<', now()->subDays($ttlDays));
            })
            ->pluck('product_id')
            ->unique()
            ->values()
            ->all();

        if (empty($staleProductIds)) {
            return 0;
        }

        $fetched = $this->client->batchGetCards($staleProductIds);

        $updated = 0;

        foreach ($fetched as $productId => $card) {
            if (! $card) {
                continue;
            }

            $attributes = Arr::except(
                PulseApiCardMapper::toInventoryAttributes($card),
                'product_id',
            );

            $updated += CardInventory::where('product_id', $productId)->update($attributes);
        }

        return $updated;
    }
}
