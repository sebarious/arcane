<?php

namespace App\Services\PulseApi;

use App\Models\CardInventory;
use App\Services\Banding\RarityBander;
use App\Support\Money;
use Illuminate\Support\Arr;

class PulseApiCardMapper
{
    protected const CACHEABLE_FIELDS = [
        'product_id', 'card_name', 'card_number', 'set_id', 'set_name', 'series',
        'release_date', 'material', 'promo_info', 'graded_by', 'grade',
        'rarity', 'rarity_rank', 'language', 'illustrator', 'pokedex_number',
        'image_url', 'slug', 'market_value_pence', 'market_value_updated_at',
        'rarity_band', 'synced_at',
    ];

    // PulseAPI's Card object has no explicit game/TCG field — the only signal is the
    // set_id, which for non-Pokémon games carries a namespaced prefix (Lorcana "lor-",
    // One Piece "op-"); plain Pokémon set codes (sv3pt5, gym2, swshp, ...) have none.
    protected const NON_POKEMON_SET_PREFIXES = ['lor-', 'op-'];

    public static function isNonPokemon(?string $setId): bool
    {
        $setId = strtolower((string) $setId);

        foreach (self::NON_POKEMON_SET_PREFIXES as $prefix) {
            if (str_starts_with($setId, $prefix)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Whether a card's language is one we currently buy — see config('selling.allowed_languages').
     */
    public static function isAllowedLanguage(?string $language): bool
    {
        $allowed = config('selling.allowed_languages', ['English']);

        return in_array($language, $allowed, true);
    }

    /**
     * Map a PulseAPI Card payload to card_inventory attributes.
     *
     * @param  array<string, mixed>  $card
     * @return array<string, mixed>
     */
    public static function toInventoryAttributes(array $card): array
    {
        $marketPounds = Arr::get($card, 'prices.market_price') ?? Arr::get($card, 'prices.market_price_global');
        $marketPence  = $marketPounds !== null ? Money::toPence($marketPounds) : null;

        return [
            'product_id'               => $card['product_id'] ?? null,
            'card_name'                => $card['card_name'] ?? '',
            'card_number'              => $card['card_number'] ?? null,
            'set_id'                   => $card['set_id'] ?? null,
            'set_name'                 => $card['set_name'] ?? null,
            'series'                   => $card['series'] ?? null,
            'release_date'             => $card['release_date'] ?? null,
            'material'                 => $card['material'] ?? null,
            'promo_info'               => $card['promo_info'] ?? null,
            'graded_by'                => $card['graded_by'] ?? null,
            'grade'                    => $card['grade'] ?? null,
            'rarity'                   => $card['rarity'] ?? null,
            'rarity_rank'              => $card['rarity_rank'] ?? null,
            'language'                 => $card['language'] ?? null,
            'illustrator'              => $card['illustrator'] ?? null,
            'pokedex_number'           => $card['pokedex_number'] ?? null,
            'image_url'                => $card['image_url'] ?? null,
            'slug'                     => $card['slug'] ?? null,
            'market_value_pence'       => $marketPence,
            // PulseAPI's own timestamp — when THEY last calculated this price upstream.
            // Purely informational; not used for our cache TTL (see `synced_at` below).
            'market_value_updated_at'  => Arr::get($card, 'prices.market_price_updated_at'),
            'rarity_band'              => (new RarityBander())->bandFor($marketPence),
            // When WE last synced this record from PulseAPI — always "now" here, since this
            // method only ever runs right after a live fetch. Deliberately ignores PulseAPI's
            // own `synced_at` field (their internal refresh cadence, not ours) — that's what
            // drives the intake cache TTL and the "Price synced" display.
            'synced_at'                => now(),
        ];
    }

    /**
     * Live PulseAPI search results, shaped for a Filament Select's search options.
     * $filters carries PulseAPI's structured search params (set_id, card_number, ...) —
     * e.g. from a separate "number" box alongside the free-text name search.
     *
     * @param  array<string, mixed>  $filters
     * @return array<string, string> product_id => display label
     */
    public static function searchOptions(string $search, array $filters = []): array
    {
        // PulseAPI requires at least one search parameter — a structured filter
        // (e.g. card_number alone) counts, so only bail if both are too thin.
        if (mb_strlen($search) < 2 && empty(array_filter($filters))) {
            return [];
        }

        $results = app(PulseApiClient::class)->search($search, self::withDefaultFilters($filters), limit: 20);

        return collect($results['data'] ?? [])
            ->mapWithKeys(fn (array $card) => [$card['product_id'] => self::searchResultLabel($card)])
            ->all();
    }

    /**
     * Auto-resolve a single best-match card from free-text name + optional number —
     * used by Rapid Intake's "Fetch card data" so rows can be plain text fields
     * instead of requiring the user to pick from a live dropdown.
     *
     * @return array<string, mixed>|null
     */
    public static function searchBestMatch(string $name, string $number = ''): ?array
    {
        $name   = trim($name);
        $number = trim($number);

        if ($name === '' && $number === '') {
            return null;
        }

        $filters = $number !== '' ? ['card_number' => $number] : [];
        $results = app(PulseApiClient::class)->search($name, self::withDefaultFilters($filters), limit: 1);

        $card = $results['data'][0] ?? null;

        return $card ? self::toInventoryAttributes($card) : null;
    }

    /**
     * Default to raw/NM stock — intake is for sealed-pack cards, not graded slabs or
     * played copies, and without this the result list is dominated by priceless graded
     * variants. Note: PulseAPI's booleans want the literal string "true", not PHP's
     * `true` (Laravel serializes that to "1", which PulseAPI silently ignores).
     *
     * @param  array<string, mixed>  $filters
     * @return array<string, mixed>
     */
    public static function withDefaultFilters(array $filters): array
    {
        return [
            'exclude_graded'      => 'true',
            'exclude_conditioned' => 'true',
            ...$filters,
        ];
    }

    /** @param  array<string, mixed>  $card  A raw PulseAPI search/get result. */
    public static function searchResultLabel(array $card): string
    {
        $priceMarketPounds = Arr::get($card, 'prices.market_price') ?? Arr::get($card, 'prices.market_price_global');

        return sprintf(
            '%s · %s · %s — %s',
            $card['card_name'] ?? 'Unknown',
            $card['set_name'] ?? '',
            $card['card_number'] ?? '',
            Money::format($priceMarketPounds !== null ? Money::toPence($priceMarketPounds) : null),
        );
    }

    /**
     * Display label for an already-selected product_id — tries the cache first,
     * falls back to a single live fetch.
     */
    public static function labelForProductId(?string $productId): ?string
    {
        if (! $productId) return null;

        $attrs = self::cachedAttributes($productId);
        if (! $attrs) {
            $card = app(PulseApiClient::class)->getCard($productId);
            if (! $card) return $productId;
            $attrs = self::toInventoryAttributes($card);
        }

        return trim("{$attrs['card_name']} · {$attrs['set_name']} · {$attrs['card_number']}", ' ·');
    }

    /**
     * Reuse a recent card_inventory row's data instead of calling PulseAPI again.
     * Returns null on a cache miss — an unseen product_id, or one whose price
     * has aged past config('services.pulseapi.price_ttl_days').
     *
     * @return array<string, mixed>|null
     */
    public static function cachedAttributes(string $productId): ?array
    {
        $ttlDays = (int) config('services.pulseapi.price_ttl_days', 5);

        $existing = CardInventory::query()
            ->where('product_id', $productId)
            ->where('synced_at', '>=', now()->subDays($ttlDays))
            ->latest('synced_at')
            ->first();

        if (! $existing) {
            return null;
        }

        return Arr::only($existing->toArray(), self::CACHEABLE_FIELDS);
    }
}
