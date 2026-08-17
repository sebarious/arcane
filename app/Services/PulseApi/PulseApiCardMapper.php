<?php

namespace App\Services\PulseApi;

use App\Enums\Game;
use App\Models\CardInventory;
use App\Services\Banding\RarityBander;
use App\Support\Money;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;

class PulseApiCardMapper
{
    public const CACHEABLE_FIELDS = [
        'product_id', 'card_name', 'card_number', 'set_id', 'set_name', 'series',
        'release_date', 'material', 'promo_info', 'graded_by', 'grade',
        'rarity', 'rarity_rank', 'language', 'illustrator', 'pokedex_number',
        'image_url', 'slug', 'market_value_pence', 'market_value_updated_at',
        'rarity_band', 'synced_at',
    ];

    /**
     * Phone-scan-only noise filter (see CardRowResolver::applySearchResolution
     * and PhoneScanController::frame) — Play! Pokémon prize-pack reprints,
     * stamped tournament promos, and jumbo oversized cards show up often
     * enough as an extra candidate on an otherwise perfectly ordinary card to
     * force "choose variant" and stall continuous phone scanning, but are rare
     * enough in real stock that excluding them from auto-resolution is the
     * right tradeoff. A card that's genuinely one of these just comes back
     * "not found" (set aside) instead — see PulseApiCardMapper::isRareVariant.
     */
    private const RARE_VARIANT_KEYWORDS = ['Play!', 'Stamp', 'Jumbo'];

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
     * Same set_id-prefix signal as isNonPokemon(), but resolved to a specific Game
     * rather than a plain yes/no — used to key per-game intake rules (see
     * isAllowedIntakeLanguage()). Only Lorcana/One Piece have a distinguishing
     * prefix today; anything else (including no prefix at all) is assumed Pokémon,
     * same assumption isNonPokemon() already makes.
     */
    public static function gameFromSetId(?string $setId): Game
    {
        $setId = strtolower((string) $setId);

        return match (true) {
            str_starts_with($setId, 'lor-') => Game::Lorcana,
            str_starts_with($setId, 'op-') => Game::OnePiece,
            default => Game::Pokemon,
        };
    }

    /**
     * Whether a card's language is one we currently buy from customers — see
     * config('selling.allowed_languages'). Used by the public Sell flow only.
     */
    public static function isAllowedLanguage(?string $language): bool
    {
        $allowed = config('selling.allowed_languages', ['English']);

        return in_array($language, $allowed, true);
    }

    /**
     * Whether a card's language is one Rapid Intake will surface as a candidate,
     * per game — see config('selling.intake_allowed_languages'). Deliberately
     * separate from isAllowedLanguage() above: intake (staff buying stock) and Sell
     * (buying from customers) don't have to accept the same languages, and Sell
     * must stay unaffected by whatever intake allows.
     */
    public static function isAllowedIntakeLanguage(?string $language, ?string $setId): bool
    {
        $game = self::gameFromSetId($setId);
        $allowed = config("selling.intake_allowed_languages.{$game->value}", ['English']);

        return in_array($language, $allowed, true);
    }

    /**
     * Whether a search candidate belongs to the game a Rapid Intake session is
     * scoped to. Only Lorcana/One Piece have a reliable set_id signal (see
     * gameFromSetId) — Magic has none yet (PulseAPI has no MTG coverage at all,
     * confirmed live), so a genuine Magic card buckets identically to Pokémon.
     * Selecting Lorcana/OnePiece strictly enforces a match; selecting
     * Pokemon/Magic accepts anything NOT positively identified as Lorcana/OnePiece,
     * rather than falsely rejecting real Magic candidates.
     */
    public static function matchesGame(array $attributes, Game $game): bool
    {
        $detected = self::gameFromSetId($attributes['set_id'] ?? null);

        if (in_array($detected, [Game::Lorcana, Game::OnePiece], true)) {
            return $detected === $game;
        }

        return in_array($game, [Game::Pokemon, Game::Magic], true);
    }

    /**
     * PulseAPI sometimes returns image URLs with a raw (not percent-encoded) unicode
     * filename segment — e.g. Chinese-exclusive sets' filenames contain the set's
     * localized name, like ".../0309-09-pokémon-horizons.webp". PHP's FILTER_VALIDATE_URL
     * rejects those outright, which Filament's ImageEntry/ImageColumn rely on internally
     * to decide a state is a URL rather than a storage-disk path — an unencoded URL
     * silently renders no thumbnail at all (though it still works if opened directly,
     * since that's plain browser navigation, not subject to PHP's stricter validation).
     * Encoding only the non-ASCII bytes (leaving normal URL syntax untouched) fixes this
     * without changing what resource the URL actually points to.
     */
    protected static function encodeImageUrl(?string $url): ?string
    {
        if ($url === null) {
            return null;
        }

        return preg_replace_callback('/[\x80-\xFF]/', fn (array $m) => rawurlencode($m[0]), $url);
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
        $marketPence = $marketPounds !== null ? Money::toPence($marketPounds) : null;

        return [
            'product_id' => $card['product_id'] ?? null,
            'card_name' => $card['card_name'] ?? '',
            'card_number' => $card['card_number'] ?? null,
            'set_id' => $card['set_id'] ?? null,
            'set_name' => $card['set_name'] ?? null,
            'series' => $card['series'] ?? null,
            'release_date' => $card['release_date'] ?? null,
            'material' => $card['material'] ?? null,
            'promo_info' => $card['promo_info'] ?? null,
            'graded_by' => $card['graded_by'] ?? null,
            'grade' => $card['grade'] ?? null,
            'rarity' => $card['rarity'] ?? null,
            'rarity_rank' => $card['rarity_rank'] ?? null,
            'language' => $card['language'] ?? null,
            'illustrator' => $card['illustrator'] ?? null,
            'pokedex_number' => $card['pokedex_number'] ?? null,
            'image_url' => self::encodeImageUrl($card['image_url'] ?? null),
            'slug' => $card['slug'] ?? null,
            'market_value_pence' => $marketPence,
            // PulseAPI's own timestamp — when THEY last calculated this price upstream.
            // Purely informational; not used for our cache TTL (see `synced_at` below).
            // Normalized here (not left as PulseAPI's raw ISO-8601 string) because this
            // array is also written via query-builder ->update() calls (CardPriceSyncer,
            // RefreshInventoryPricesCommand), which bypass the model's `datetime` cast
            // entirely — an un-normalized value reaches the DB as-is, and MySQL's DATETIME
            // columns reject PulseAPI's "...T00:00:00.000Z" format outright.
            'market_value_updated_at' => self::parseTimestamp(Arr::get($card, 'prices.market_price_updated_at')),
            'rarity_band' => (new RarityBander)->bandFor($marketPence),
            // When WE last synced this record from PulseAPI — always "now" here, since this
            // method only ever runs right after a live fetch. Deliberately ignores PulseAPI's
            // own `synced_at` field (their internal refresh cadence, not ours) — that's what
            // drives the intake cache TTL and the "Price synced" display.
            'synced_at' => now(),
        ];
    }

    /**
     * PulseAPI's timestamp is untrusted external input — malformed or unexpected
     * formats fall back to null rather than blowing up card sync/batch generation
     * over a field that's purely informational (see toInventoryAttributes above).
     */
    private static function parseTimestamp(mixed $value): ?string
    {
        if (blank($value)) {
            return null;
        }

        try {
            return Carbon::parse($value)->toDateTimeString();
        } catch (\Throwable) {
            return null;
        }
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
     * Resolve every plausible match for a free-text name + optional number — used by
     * Rapid Intake's "Fetch card data" so rows can be plain text fields instead of
     * requiring the user to pick from a live dropdown while still searching. A name +
     * number can genuinely match several distinct prints (Pokémon Center exclusives,
     * stamped/staff variants, etc.) — the caller decides what to do when there's more
     * than one (auto-resolve on an unambiguous single match, otherwise let the user
     * choose rather than silently guessing).
     *
     * @return array<int, array<string, mixed>>
     */
    public static function searchCandidates(string $name, string $number, Game $game, int $limit = 8): array
    {
        $name = trim($name);
        $number = trim($number);

        if ($name === '' && $number === '') {
            return [];
        }

        $filters = $number !== '' ? ['card_number' => $number] : [];
        $results = app(PulseApiClient::class)->search($name, self::withDefaultFilters($filters), limit: $limit);

        // A shared collector number is very often a coincidence across sets rather
        // than the same print (see setCodeFromImageUrl's caveat above — PulseAPI
        // exposes no printed set-code field to disambiguate by) — most commonly a
        // Japan-exclusive set reusing the same number as the English print we
        // actually stock. We only ever buy intake-allowed-language stock per game
        // (see config('selling.intake_allowed_languages') — Pokémon includes Chinese
        // for Chinese-exclusive sets), so cutting those before the ambiguity check
        // below resolves a lot of "ambiguous" numbers straight to the one real match.
        return collect($results['data'] ?? [])
            ->map(fn (array $card) => self::toInventoryAttributes($card))
            ->filter(fn (array $attributes) => self::isAllowedIntakeLanguage($attributes['language'] ?? null, $attributes['set_id'] ?? null))
            ->filter(fn (array $attributes) => self::matchesGame($attributes, $game))
            ->values()
            ->all();
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
            'exclude_graded' => 'true',
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
     * Display label for one candidate in a disambiguation list (see searchCandidates).
     * Rapid Intake's live scanner searches by number alone (a name pulled from OCR
     * proved too unreliable — see CardNumberExtractor/CardRowResolver history), so a
     * shared number can surface completely different Pokémon across different sets,
     * not just different finishes/prints of the same card — the name has to lead,
     * not just whatever distinguishes prints of what was assumed to be one card.
     *
     * @param  array<string, mixed>  $attributes  Already mapped via toInventoryAttributes().
     */
    public static function candidateLabel(array $attributes): string
    {
        $descriptors = array_filter([
            $attributes['material'] ?? null,
            $attributes['promo_info'] ?? null,
            ($attributes['graded_by'] ?? null) && ($attributes['grade'] ?? null)
                ? "{$attributes['graded_by']} {$attributes['grade']}"
                : null,
        ]);

        return sprintf(
            '%s — %s%s — %s',
            $attributes['card_name'] ?? 'Unknown card',
            $attributes['set_name'] ?? 'Unknown set',
            $descriptors ? ' · '.implode(' · ', $descriptors) : ' · Standard',
            Money::format($attributes['market_value_pence'] ?? null),
        );
    }

    /**
     * A handful of image folders are the full set name rather than the short
     * code actually printed on the card — confirmed for White Flare / Black
     * Bolt, whose shared "/086" numbering otherwise makes them indistinguishable
     * to CardRowResolver's set-code disambiguation (searchCandidates alone
     * returns both as candidates for the same number; without this, comparing
     * against the OCR-read "WHT"/"BLK" never matches "WHITE-FLARE"/"BLACK-BOLT",
     * so it silently falls back to "ambiguous" every time instead of narrowing
     * to the one real match).
     *
     * @var array<string, string>
     */
    private const IMAGE_FOLDER_CODE_OVERRIDES = [
        'WHITE-FLARE' => 'WHT',
        'BLACK-BOLT' => 'BLK',
    ];

    /**
     * Pulls the folder segment out of a card's image URL, e.g.
     * ".../cards/images/DRI/Crustle_186_182.webp" -> "DRI" — this happens to
     * match the official set code printed on modern cards, even though
     * PulseAPI's own `set_id` uses a different, internal scheme (e.g. "sv10"
     * for that same set). Confirmed against several real sets; not documented
     * by PulseAPI, so this is a best-effort match, not a guaranteed contract —
     * callers should treat a null/mismatch as "no signal", not an error. A
     * small number of folders don't follow this convention at all (spelled-out
     * full names instead of a code) — see IMAGE_FOLDER_CODE_OVERRIDES.
     */
    public static function setCodeFromImageUrl(?string $imageUrl): ?string
    {
        if (! $imageUrl || ! preg_match('#/cards/images/([^/]+)/#i', $imageUrl, $m)) {
            return null;
        }

        $folder = strtoupper($m[1]);

        return self::IMAGE_FOLDER_CODE_OVERRIDES[$folder] ?? $folder;
    }

    /**
     * @param  array<string, mixed>  $attributes  Already mapped via toInventoryAttributes().
     */
    public static function isRareVariant(array $attributes): bool
    {
        $haystack = strtolower(($attributes['material'] ?? '').' '.($attributes['promo_info'] ?? ''));

        foreach (self::RARE_VARIANT_KEYWORDS as $keyword) {
            if (str_contains($haystack, strtolower($keyword))) {
                return true;
            }
        }

        return false;
    }

    /**
     * Display label for an already-selected product_id — tries the cache first,
     * falls back to a single live fetch.
     */
    public static function labelForProductId(?string $productId): ?string
    {
        if (! $productId) {
            return null;
        }

        $attrs = self::cachedAttributes($productId);
        if (! $attrs) {
            $card = app(PulseApiClient::class)->getCard($productId);
            if (! $card) {
                return $productId;
            }
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
