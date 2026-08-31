<?php

namespace App\Models;

use App\Enums\Game;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RipPack extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'description',
        'image_path',
        'price_pence',
        'games',
        'band_odds',
        'buy_back_percentage',
        'status',
    ];

    protected $casts = [
        'price_pence' => 'integer',
        'games' => 'array',
        'band_odds' => 'array',
        'buy_back_percentage' => 'float',
    ];

    public function rips(): HasMany
    {
        return $this->hasMany(Rip::class);
    }

    /**
     * Rewrites the raw uploaded disk path into a full display URL — same
     * pattern as Store::getLogoAttribute(). Null when no custom art was
     * uploaded; the frontend/admin table both fall back to the default
     * Arcane bag art in that case, not here, since the fallback asset lives
     * in two different places (a Vite-bundled import client-side, a public
     * build path in Filament).
     */
    public function getImagePathAttribute(): ?string
    {
        $raw = $this->attributes['image_path'] ?? null;

        return $raw ? route('image.show', ['path' => $raw]) : null;
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    /** @return Game[] */
    public function gameEnums(): array
    {
        return array_map(fn (string $value) => Game::from($value), $this->games ?? []);
    }
}
