<?php

namespace App\Services\PulseApi;

use App\Models\CardInventory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;

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
            ->where('price_locked', false)
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

            // A product_id can be shared by rows outside $scope too (e.g. a locked
            // copy elsewhere in inventory) — re-excluding price_locked here, not just
            // above, keeps a locked row's price untouched no matter which copy of it
            // triggered this refresh.
            $updated += CardInventory::where('product_id', $productId)
                ->where('price_locked', false)
                ->update($attributes);
        }

        return $updated;
    }

    /**
     * Unlike syncStale(), refreshes every given card regardless of how recently
     * it was last synced — used right after a batch's candidate selection, when
     * the specific cards about to be allocated need to be priced against Pulse
     * right now, not whatever happened to be true whenever they last synced
     * (see BatchGenerator::verifyAndBackfillPrices — that's what catches a card
     * whose price has since moved it into a different rarity_band than the slot
     * it was selected for). Price-locked cards are skipped entirely — never
     * sent to Pulse, never in the returned set — same guarantee syncStale()
     * makes.
     *
     * @param  Collection<int, CardInventory>  $cards
     * @return array<string, true> product_id => successfully verified against Pulse just now
     */
    public function forceRefresh(Collection $cards): array
    {
        $productIds = $cards
            ->reject(fn (CardInventory $c) => $c->price_locked)
            ->pluck('product_id')
            ->filter()
            ->unique()
            ->values()
            ->all();

        if (empty($productIds)) {
            return [];
        }

        $fetched = $this->client->batchGetCards($productIds);
        $verified = [];

        foreach ($fetched as $productId => $card) {
            if (! $card) {
                continue;
            }

            $attributes = Arr::except(
                PulseApiCardMapper::toInventoryAttributes($card),
                'product_id',
            );

            CardInventory::where('product_id', $productId)
                ->where('price_locked', false)
                ->update($attributes);

            $verified[$productId] = true;
        }

        return $verified;
    }
}
