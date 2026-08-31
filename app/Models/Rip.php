<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Rip extends Model
{
    protected $fillable = [
        'rip_order_id',
        'rip_pack_id',
        'pack_name',
        'price_pence',
        'buy_back_percentage',
        'verification_snapshot_path',
        'card_inventory_id',
        'opened_at',
        'decision',
        'decided_at',
        'sold_back_pence',
        'dispatched_at',
        'dispatched_by_user_id',
        // Not verification_seed/verification_hash/verification_committed_at —
        // those are only ever set directly in booted()'s creating hook below,
        // never mass-assigned. Same convention as Batch.
    ];

    protected $casts = [
        'price_pence' => 'integer',
        'buy_back_percentage' => 'float',
        'opened_at' => 'datetime',
        'decided_at' => 'datetime',
        'sold_back_pence' => 'integer',
        'dispatched_at' => 'datetime',
    ];

    /**
     * Seed/hash committed the moment this row exists — before payment even
     * completes, let alone before the draw runs (see RipDrawer) — so the
     * published hash provably predates any event that could have influenced
     * what the draw would produce. Exactly mirrors Batch's own booted() hook.
     */
    protected static function booted(): void
    {
        static::creating(function (Rip $rip) {
            $seed = bin2hex(random_bytes(32));

            $rip->verification_seed = $seed;
            $rip->verification_hash = hash('sha256', $seed);
            $rip->verification_committed_at = now();
        });
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(RipOrder::class, 'rip_order_id');
    }

    public function pack(): BelongsTo
    {
        return $this->belongsTo(RipPack::class, 'rip_pack_id');
    }

    public function card(): BelongsTo
    {
        return $this->belongsTo(CardInventory::class, 'card_inventory_id');
    }

    public function dispatchedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'dispatched_by_user_id');
    }

    public function isDrawn(): bool
    {
        return $this->card_inventory_id !== null;
    }

    public function isOpened(): bool
    {
        return $this->opened_at !== null;
    }

    public function isDecided(): bool
    {
        return $this->decision !== null;
    }

    public function isDispatched(): bool
    {
        return $this->dispatched_at !== null;
    }

    /** Needs physically posting — kept, but not yet marked as dispatched. */
    public function needsDispatch(): bool
    {
        return $this->decision === 'kept' && ! $this->isDispatched();
    }

    /**
     * The shape Rips/Order.vue's stacked-popup opener needs for one pack —
     * shared by OrderController (one purchase's worth) and
     * UnopenedRipsController (every unopened pack across all of a
     * customer's orders), so both feed the exact same component the exact
     * same data shape.
     */
    public function toStackArray(): array
    {
        return [
            'id' => $this->id,
            'pack_name' => $this->pack_name,
            'price_pence' => $this->price_pence,
            'buy_back_percentage' => $this->buy_back_percentage,
            'ready' => $this->isDrawn(),
            'opened' => $this->isOpened(),
            'decision' => $this->decision,
            'sold_back_pence' => $this->sold_back_pence,
            'card' => $this->isOpened() ? [
                'name' => $this->card?->card_name,
                'set' => $this->card?->set_name,
                'number' => $this->card?->card_number,
                'image' => $this->card?->image_url,
                'band' => $this->card?->rarity_band,
                'market_value_pence' => $this->card?->market_value_pence,
            ] : null,
        ];
    }
}
