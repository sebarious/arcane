<?php

namespace App\Models;

use App\Enums\Game;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class CardInventory extends Model
{
    protected $table = 'card_inventory';

    protected $fillable = [
        'condition', 'cost_pence', 'acquired_at', 'acquired_from',
        'acquisition_lot', 'market_value_pence', 'market_value_updated_at', 'price_locked',
        'rarity_band', 'pack_id', 'qr_token', 'status',
        'allocated_sale_price_pence', 'margin_pence',
        'delisted_at', 'delisted_by_user_id', 'game', 'picked_at', 'reserved_until', 'reserved_by',
        'on_ebay', 'in_card_wall', 'not_for_batches',
        // PulseAPI card data
        'product_id', 'card_name', 'card_number', 'set_id', 'set_name', 'series',
        'release_date', 'material', 'promo_info', 'graded_by', 'grade',
        'rarity', 'rarity_rank', 'language', 'illustrator', 'pokedex_number',
        'image_url', 'custom_image_path', 'slug', 'synced_at',
    ];

    protected $casts = [
        'acquired_at' => 'date',
        'market_value_updated_at' => 'datetime',
        'delisted_at' => 'datetime',
        'release_date' => 'date',
        'synced_at' => 'datetime',
        'picked_at' => 'datetime',
        'reserved_until' => 'datetime',
        'game' => Game::class,
        'price_locked' => 'boolean',
        'on_ebay' => 'boolean',
        'in_card_wall' => 'boolean',
        'not_for_batches' => 'boolean',
    ];

    public function pack()
    {
        return $this->belongsTo(Pack::class);
    }

    public function delistedBy()
    {
        return $this->belongsTo(User::class, 'delisted_by_user_id');
    }

    // Scopes
    public function scopeInStock($q)
    {
        return $q->where('status', 'in_stock');
    }

    public function scopeUnsold($q)
    {
        return $q->whereIn('status', ['allocated', 'dispatched']);
    }

    /**
     * What we actually hold as loose stock: in stock, and not committed to a
     * pack. Distinct from inStock(), which is the status on its own — status
     * and pack_id are only kept in step by convention (BatchGenerator sets
     * both together), so counting on the status alone would quietly overstate
     * the shelf the moment anything left the two disagreeing.
     *
     * Deliberately still counts cards held in a kiosk basket: a 15-minute
     * reservation is someone mid-purchase, not stock leaving the building,
     * and excluding them would make dashboard totals flicker as people
     * browse. Use available() where a live hold does matter.
     */
    public function scopeStockOnHand($q)
    {
        return $q->where('status', 'in_stock')->whereNull('pack_id');
    }

    public function scopeForStore($q, int $storeId)
    {
        return $q->whereHas('pack.batch', fn ($b) => $b->where('store_id', $storeId));
    }

    /**
     * Physically unclaimed stock — in the warehouse, not earmarked for a
     * mystery pack, and not currently sitting in someone else's kiosk basket.
     * The single definition of "available" shared by BatchGenerator's
     * candidate pool and kiosk search/reservation, so the two channels can
     * never both think they own the same physical card.
     */
    public function scopeAvailable($q)
    {
        return $q->where('status', 'in_stock')
            ->whereNull('pack_id')
            ->where(fn ($q) => $q->whereNull('reserved_until')->orWhere('reserved_until', '<', now()));
    }

    /**
     * Cards that may be sealed into a mystery pack at all, regardless of
     * whether they happen to be free to allocate right now. Two things
     * disqualify a card:
     *
     *   - not_for_batches: held back on condition.
     *   - graded: a slab can't go in a pack, and the grade is most of what
     *     the buyer is paying for — it belongs on the kiosk, card wall or
     *     eBay where they can see exactly what they're getting.
     *
     * Graded is derived from the columns rather than needing the flag set by
     * hand (see isGraded()), so a card graded by PulseAPI or by an admin is
     * out of batches the moment it's marked, with nothing to remember.
     */
    public function scopeBatchable($q)
    {
        return $q->where('not_for_batches', false)->whereNot(fn ($q) => $q->whereGraded());
    }

    /** The inverse of batchable() — what the non-batch inventory view lists. */
    public function scopeNotBatchable($q)
    {
        return $q->whereNot(fn ($q) => $q->where('not_for_batches', false)->whereNot(fn ($q) => $q->whereGraded()));
    }

    /** Both grading fields present and non-empty — the SQL form of isGraded(). */
    public function scopeWhereGraded($q)
    {
        return $q->whereNotNull('graded_by')->where('graded_by', '<>', '')
            ->whereNotNull('grade')->where('grade', '<>', '');
    }

    /**
     * Available stock that may also be drawn into a generated batch — i.e.
     * everything available(), minus anything batchable() rules out.
     *
     * The kiosk, card wall and eBay deliberately use available() instead: a
     * card can be unfit to seal into a mystery pack (where the buyer can't
     * see what they're getting) and still be an honest sale face-up, where
     * they can. Every batch-building path should go through this, so the
     * exclusions can't be quietly bypassed by a new query.
     */
    public function scopeBatchEligible($q)
    {
        return $q->available()->batchable();
    }

    /**
     * Once a card leaves the available() pool — allocated to a pack,
     * dispatched, sold, written off, anything other than sitting unclaimed
     * in stock — its rarity_band is frozen for good. A pack sealed and
     * promised to a customer as "common" must never quietly become "rare"
     * days later just because the live market price drifted; the band
     * printed on the card list is the one that's owed. market_value_pence
     * itself is unaffected by this — it can keep refreshing for reporting
     * purposes, this only protects the band classification. Checked by
     * every price-sync write path (CardPriceSyncer, PulseApiPriceProvider,
     * the manual "edit market value" admin form) before touching the band.
     */
    public function isBandLocked(): bool
    {
        return ! ($this->status === 'in_stock' && $this->pack_id === null);
    }

    /**
     * Chaos storage: a card's physical spot is purely its alphabetical rank
     * (name, then set, then id to break ties) among still-in-box cards in its
     * lot — see App\Services\Batches\PickingSheetGenerator. Used wherever a
     * sheet needs to read out cards in the exact order they sit in the box, so
     * the picking sheet and the QR/packing sheet (GenerateBatchQrSheetJob)
     * never disagree on order. The DB-side equivalent is
     * "LOWER(card_name) asc, LOWER(set_name) asc, id asc" — keep both in sync.
     */
    public function chaosSortKey(): string
    {
        return strtolower(($this->card_name ?? '').'|'.($this->set_name ?? '').'|'.str_pad((string) $this->id, 10, '0', STR_PAD_LEFT));
    }

    // QR token generation — short, URL-safe, unguessable.
    public static function generateQrToken(): string
    {
        do {
            $token = Str::lower(Str::random(12));
        } while (static::where('qr_token', $token)->exists());

        return $token;
    }

    // Money accessors (pence -> pounds)
    public function getCostAttribute(): float
    {
        return $this->cost_pence / 100;
    }

    public function getMarketValueAttribute(): ?float
    {
        return $this->market_value_pence !== null ? $this->market_value_pence / 100 : null;
    }

    public function getValuePenceAttribute(): int
    {
        return (int) ($this->market_value_pence ?? $this->cost_pence ?? 0);
    }

    /**
     * Storefront badges (e.g. "Pokémon Center", "Stamped") derived from
     * substrings PulseAPI embeds in product_id — there's no dedicated field
     * for these variants, so this is the one place that maps the raw string
     * to a display label. A card can carry more than one.
     */
    public function getProductBadgesAttribute(): array
    {
        $badges = [];

        if ($this->isGraded()) {
            // Leads the list — on a slab the grade is the headline fact, and
            // this is what the kiosk and storefront print on the card tile.
            $badges[] = trim("{$this->graded_by} {$this->grade}");
        }

        if ($this->product_id && str_contains($this->product_id, 'Pokémon Center')) {
            $badges[] = 'Pokémon Center';
        }

        if ($this->product_id && str_contains($this->product_id, 'Stamp')) {
            $badges[] = 'Stamped';
        }

        return $badges;
    }

    /**
     * Graded is derived, not stored: a card is graded exactly when it carries
     * both a grader and a grade. Keeping a separate boolean would just be a
     * third thing to disagree with those two — and PulseAPI populates them on
     * slabs it knows about, without any flag for us to set.
     */
    public function isGraded(): bool
    {
        return filled($this->graded_by) && filled($this->grade);
    }

    /** Whether this card could ever go in a batch — the row-level form of scopeBatchable(). */
    public function isBatchable(): bool
    {
        return ! $this->not_for_batches && ! $this->isGraded();
    }

    /**
     * Our own photo when we've taken one, otherwise PulseAPI's stock artwork.
     *
     * Deliberately an override of image_url rather than a new field the rest
     * of the app has to know about — kiosk, storefront, picking sheets and
     * the admin tables all read image_url already, so a graded slab's real
     * photo reaches every one of them without touching any of them. The raw
     * PulseAPI value is still there via getRawOriginal('image_url'), which is
     * what the upload field needs to show its current file (same pattern as
     * Store::getLogoAttribute()).
     */
    public function getImageUrlAttribute(?string $value): ?string
    {
        if (filled($this->custom_image_path)) {
            return Storage::disk('public')->url($this->custom_image_path);
        }

        return $value;
    }
}
