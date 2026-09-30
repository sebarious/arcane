<?php

namespace App\Services\Kiosk;

use App\Models\CardInventory;
use App\Services\Banding\RarityBander;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

/**
 * The single definition of what the kiosk is allowed to offer right now:
 * available stock, narrowed by whatever the customer has filtered to, with
 * duplicate printings collapsed down to one row each.
 *
 * Shared by search and browse so the two can't drift apart — a card being
 * findable by name but missing from A-Z (or counted differently by each)
 * would be its own bug.
 */
class KioskStockQuery
{
    /**
     * @param  array{search?: ?string, letter?: ?string, set?: ?string, rarity?: ?string, featured?: bool}  $filters
     */
    public function build(array $filters = []): Builder
    {
        // Stock runs about three copies deep on popular printings, which
        // buried the rest of the catalogue under repeats of the same card.
        // Only the priciest copy of each product is offered; the others stay
        // in stock and surface as soon as it's sold or held by someone.
        //
        // ROW_NUMBER() rather than a GROUP BY + join because copies of a
        // printing usually carry an identical market value — the id tiebreak
        // is what stops the "winner" shuffling between requests, which would
        // make paging skip or repeat rows. Both MySQL 8 and Postgres support
        // this; the filters live inside the subquery so the copy that wins is
        // the best one *within* the current filter, not one that's since been
        // filtered out.
        $ranked = $this->filtered($filters)
            ->select('id')
            ->selectRaw('ROW_NUMBER() OVER (PARTITION BY product_id ORDER BY market_value_pence DESC, id ASC) AS rn');

        $bestPerProduct = DB::query()
            ->fromSub($ranked, 'ranked')
            ->where('rn', 1)
            ->select('id');

        return CardInventory::query()->whereIn('id', $bestPerProduct);
    }

    /**
     * The sets and rarities actually present in sellable stock — what the
     * filter pickers offer, so the customer can never pick a combination
     * that returns nothing.
     *
     * @return array{sets: list<string>, rarities: list<string>}
     */
    public function filterOptions(): array
    {
        $sets = CardInventory::query()
            ->available()
            ->whereNotNull('set_name')
            ->distinct()
            ->orderBy('set_name')
            ->pluck('set_name')
            ->all();

        $present = CardInventory::query()
            ->available()
            ->whereNotNull('rarity_band')
            ->distinct()
            ->pluck('rarity_band')
            ->all();

        // Cheapest band first, as everywhere else — alphabetical would put
        // chase between common and legendary, which reads as nonsense.
        $rarities = array_values(array_filter(
            array_keys(RarityBander::DEFAULT_THRESHOLDS),
            fn (string $band) => in_array($band, $present, true),
        ));

        return ['sets' => array_values($sets), 'rarities' => $rarities];
    }

    /**
     * A deterministic shuffle: the same seed always produces the same order,
     * which is what stops infinite scroll skipping or repeating cards between
     * pages the way a plain random sort would. MOD() behaves the same on
     * MySQL and Postgres; the id sort just breaks ties stably.
     */
    public function applyFeaturedOrder(Builder $query, int $seed): Builder
    {
        return $query->orderByRaw('MOD(id * ?, 104729)', [max(1, $seed)])->orderBy('id');
    }

    /**
     * Release date of the Nth most recent set in stock — the floor for what
     * counts as "recent" on the landing view. Falls back to the oldest stock
     * we hold if there aren't that many sets, so the featured view is never
     * empty just because the catalogue is small.
     */
    private function recentSetCutoff(): ?string
    {
        $dates = CardInventory::query()
            ->available()
            ->whereNotNull('release_date')
            ->distinct()
            ->orderByDesc('release_date')
            ->limit(max(1, (int) config('kiosk.featured_recent_sets', 8)))
            ->pluck('release_date');

        return $dates->last();
    }

    /** @param  array{search?: ?string, letter?: ?string, set?: ?string, rarity?: ?string, featured?: bool}  $filters */
    private function filtered(array $filters): Builder
    {
        return CardInventory::query()
            ->available()
            ->when($filters['featured'] ?? false, function (Builder $q) {
                $cutoff = $this->recentSetCutoff();

                if ($cutoff !== null) {
                    $q->where('release_date', '>=', $cutoff);
                }
            })
            ->when($filters['search'] ?? null, function (Builder $q, string $term) {
                $like = '%'.strtolower($term).'%';

                $q->where(function (Builder $inner) use ($like) {
                    $inner->whereRaw('LOWER(card_name) LIKE ?', [$like])
                        ->orWhereRaw('LOWER(set_name) LIKE ?', [$like])
                        ->orWhereRaw('LOWER(card_number) LIKE ?', [$like]);
                });
            })
            ->when($filters['letter'] ?? null, fn (Builder $q, string $letter) => $q->whereRaw('LOWER(card_name) LIKE ?', [strtolower($letter).'%']))
            ->when($filters['set'] ?? null, fn (Builder $q, string $set) => $q->where('set_name', $set))
            ->when($filters['rarity'] ?? null, fn (Builder $q, string $rarity) => $q->where('rarity_band', $rarity));
    }
}
