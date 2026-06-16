<?php

namespace App\Models;

use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Model;
use App\Enums\Game;

class CardInventory extends Model
{
    protected $table = 'card_inventory';

    protected $fillable = [
        'card_id', 'condition', 'cost_pence', 'acquired_at', 'acquired_from',
        'acquisition_lot', 'market_value_pence', 'market_value_updated_at',
        'rarity_band', 'pack_id', 'qr_token', 'status',
        'allocated_sale_price_pence', 'margin_pence',
        'delisted_at', 'delisted_by_user_id', 'game'
    ];

    protected $casts = [
        'acquired_at'             => 'date',
        'market_value_updated_at' => 'datetime',
        'delisted_at'             => 'datetime',
        'game'                    => Game::class,
    ];

    public function card()        { return $this->belongsTo(Card::class); }
    public function pack()        { return $this->belongsTo(Pack::class); }
    public function delistedBy()  { return $this->belongsTo(User::class, 'delisted_by_user_id'); }

    // Scopes
    public function scopeInStock($q)   { return $q->where('status', 'in_stock'); }
    public function scopeUnsold($q)    { return $q->whereIn('status', ['allocated', 'dispatched']); }
    public function scopeForStore($q, int $storeId)
    {
        return $q->whereHas('pack.batch', fn ($b) => $b->where('store_id', $storeId));
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
    public function getCostAttribute(): float        { return $this->cost_pence / 100; }
    public function getMarketValueAttribute(): ?float
    {
        return $this->market_value_pence !== null ? $this->market_value_pence / 100 : null;
    }

    public function getValuePenceAttribute(): int
    {
        return (int) ($this->market_value_pence ?? $this->cost_pence ?? 0);
    }
}
