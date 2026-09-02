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

        $staleCards = (clone $scope)
            ->whereNotNull('product_id')
            ->where('price_locked', false)
            ->where(function (Builder $query) use ($ttlDays) {
                $query->whereNull('synced_at')
                    ->orWhere('synced_at', '<', now()->subDays($ttlDays));
            })
            ->get(['id', 'product_id']);

        if ($staleCards->isEmpty()) {
            return 0;
        }

        // A product_id can be (and by design, per the duplicate-limit logic
        // elsewhere, is) shared by rows well outside $scope too — a copy
        // already allocated to a pack in a different batch, dispatched, or
        // sold entirely unrelated to this refresh. Grouping ids by
        // product_id here, and scoping every write below to exactly those
        // ids, means this can never fan out and touch (let alone reband —
        // see CardInventory::isBandLocked()) a row $scope never intended.
        $idsByProductId = $staleCards->groupBy('product_id')->map(fn ($rows) => $rows->pluck('id'));

        $fetched = $this->client->batchGetCards($idsByProductId->keys()->all());

        $updated = 0;

        foreach ($fetched as $productId => $card) {
            if (! $card) {
                continue;
            }

            $ids = $idsByProductId->get($productId, collect());

            if ($ids->isEmpty()) {
                continue;
            }

            $attributes = Arr::except(
                PulseApiCardMapper::toInventoryAttributes($card),
                'product_id',
            );

            $updated += CardInventory::whereIn('id', $ids)
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
        // Same reasoning as syncStale() — scope every write to exactly the
        // ids of the cards actually passed in, not the blanket product_id,
        // so an unrelated row that happens to share it (already allocated/
        // dispatched/sold elsewhere) is never touched by this call.
        $idsByProductId = $cards
            ->reject(fn (CardInventory $c) => $c->price_locked || ! $c->product_id)
            ->groupBy('product_id')
            ->map(fn ($rows) => $rows->pluck('id'));

        if ($idsByProductId->isEmpty()) {
            return [];
        }

        $fetched = $this->client->batchGetCards($idsByProductId->keys()->all());
        $verified = [];

        foreach ($fetched as $productId => $card) {
            if (! $card) {
                continue;
            }

            $ids = $idsByProductId->get($productId, collect());

            if ($ids->isEmpty()) {
                continue;
            }

            $attributes = Arr::except(
                PulseApiCardMapper::toInventoryAttributes($card),
                'product_id',
            );

            CardInventory::whereIn('id', $ids)
                ->where('price_locked', false)
                ->update($attributes);

            $verified[$productId] = true;
        }

        return $verified;
    }
}
