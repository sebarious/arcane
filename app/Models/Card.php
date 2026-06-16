<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Services\Banding\RarityBander;
use App\Enums\Game;
use Illuminate\Support\Facades\URL;

class Card extends Model
{
    protected $fillable = [
        'name', 'set_code', 'set_name', 'card_number', 'variant',
        'language', 'printed_rarity', 'image_front', 'image_back', 'external_ids', 'game',
        'scrydex_id'
    ];

    protected $casts = [
        'external_ids' => 'array',
        'game' => Game::class,
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

    public function getCurrentMarketPenceAttribute(): ?int
    {
        return $this->latestPrice('seed')?->median_pence
            ?? $this->latestPrice('scrydex')?->median_pence
            ?? $this->latestPrice('cardmarket')?->median_pence
            ?? $this->latestPrice('tcgplayer')?->median_pence;
    }

    public function getImageFrontAttribute(): ?string
    {
        return $this->attributes['image_front'] ? URL::temporarySignedRoute('image.show', now()->addMinutes(5), ['path' => $this->attributes['image_front']]) : null;
    }

    public function addToInventory(
        int $costPence,
        \DateTimeInterface|string $acquiredAt,
        ?string $acquiredFrom = null,
        ?string $acquisitionLot = null,
        int $quantity = 1,
    ): \Illuminate\Database\Eloquent\Collection {
        // Prefer latest market snapshot, fallback to cost if none
        $marketPence = $this->current_market_pence; // from Card::latestPrice()
        if ($marketPence === null) {
            // Conservative fallback: assume market ~= cost
            $marketPence = $costPence;
        }
        $bander = new RarityBander();
        $band   = $bander->bandFor($marketPence);
        $rows = [];
        for ($i = 0; $i < $quantity; $i++) {
            $rows[] = $this->inventory()->create([
                'game'                    => $this->game->value, // if you added game
                'condition'              => 'NM',
                'cost_pence'             => $costPence,
                'acquired_at'            => $acquiredAt,
                'acquired_from'          => $acquiredFrom,
                'acquisition_lot'        => $acquisitionLot,
                'market_value_pence'     => $marketPence,
                'market_value_updated_at' => now(),
                'rarity_band'            => $band,
                'status'                 => 'in_stock',
            ]);
        }
        return new \Illuminate\Database\Eloquent\Collection($rows);
    }
}
