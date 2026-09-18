<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PackagingStock extends Model
{
    protected $fillable = ['key', 'quantity_on_hand'];

    /**
     * Every row that should exist, seeded once in the create-table migration
     * (bags/inserts) or a later one (toploaders/sleeves, then insert_chase —
     * see the migrations dated 2026_09_18 and after). Bags and inserts
     * (insert_chase included, once a rarity_band of 'chase' exists — see
     * config/banding.php's Premium entry) are deducted automatically by
     * BatchGenerator; toploaders and sleeves are tracked the same way but
     * only ever adjusted manually (see PackagingStockResource::
     * adjustStockAction) — there's no per-batch formula for them.
     */
    public const LABELS = [
        'bag' => 'Arcane bags',
        'insert_common' => 'Common inserts',
        'insert_rare' => 'Rare inserts',
        'insert_super' => 'Super inserts',
        'insert_legendary' => 'Legendary inserts',
        'insert_mythic' => 'Mythic inserts',
        'insert_chase' => 'Chase inserts',
        'toploader' => 'Toploaders',
        'sleeve' => 'Sleeves',
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
