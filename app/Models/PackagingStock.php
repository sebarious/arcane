<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PackagingStock extends Model
{
    protected $fillable = ['key', 'quantity_on_hand'];

    /** Every row that should exist, seeded once in the create-table migration. */
    public const LABELS = [
        'bag' => 'Arcane bags',
        'insert_common' => 'Common inserts',
        'insert_rare' => 'Rare inserts',
        'insert_super' => 'Super inserts',
        'insert_legendary' => 'Legendary inserts',
        'insert_mythic' => 'Mythic inserts',
    ];

    public function movements(): HasMany
    {
        return $this->hasMany(PackagingStockMovement::class);
    }

    public function getLabelAttribute(): string
    {
        return self::LABELS[$this->key] ?? $this->key;
    }

    /** The insert key for a rarity band, e.g. 'rare' => 'insert_rare'. */
    public static function insertKeyForBand(string $band): string
    {
        return "insert_{$band}";
    }
}
