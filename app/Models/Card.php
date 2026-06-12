<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Services\Banding\RarityBander;
use App\Enums\Game;

class Card extends Model
{
    protected $fillable = [
        'name', 'set_code', 'set_name', 'card_number', 'variant',
        'language', 'printed_rarity', 'image_front', 'image_back', 'external_ids', 'game'
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

    public function getCurrentMarketPencesAttribute(): ?int
    {
        return $this->latestPrice('seed')?->median_pence
            ?? $this->latestPrice('scrydex')?->median_pence
            ?? $this->latestPrice('cardmarket')?->median_pence
            ?? $this->latestPrice('tcgplayer')?->median_pence;
    }

    public function addToInventory(
        int $costPence,
        \DateTimeInterface|string $acquiredAt,
        ?string $acquiredFrom = null,
        ?string $acquisitionLot = null,
        int $quantity = 1,
    ): \Illuminate\Database\Eloquent\Collection
    {
        $marketPence = $this->current_market_pence;
        $band        = (new RarityBander())->bandFor($marketPence);
        $rows = [];
        for ($i = 0; $i < $quantity; $i++) {
            $rows[] = $this->inventory()->create([
                'game'                    => $this->game->value,
                'condition'               => 'NM',
                'cost_pence'              => $costPence,
                'acquired_at'             => $acquiredAt,
                'acquired_from'           => $acquiredFrom,
                'acquisition_lot'         => $acquisitionLot,
                'market_value_pence'      => $marketPence,
                'market_value_updated_at' => now(),
                'rarity_band'             => $band,
                'status'                  => 'in_stock',
            ]);
        }
        return new \Illuminate\Database\Eloquent\Collection($rows);
    }
}
