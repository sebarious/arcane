<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Card extends Model
{
    protected $fillable = [
        'name', 'set_code', 'set_name', 'card_number', 'variant',
        'language', 'printed_rarity', 'image_front', 'image_back', 'external_ids',
    ];

    protected $casts = [
        'external_ids' => 'array',
    ];

    public function inventory()        { return $this->hasMany(CardInventory::class); }

    public function priceSnapshots()   { return $this->hasMany(MarketPriceSnapshot::class); }
    
    public function latestPrice(string $source = 'cardmarket'): ?MarketPriceSnapshot
    {
        return $this->priceSnapshots()
            ->where('source', $source)
            ->latest('fetched_at')
            ->first();
    }
}
