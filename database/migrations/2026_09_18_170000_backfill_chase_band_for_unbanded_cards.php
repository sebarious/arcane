<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // The chase band is new (Premium batches — see config/banding.php), so
        // cards priced in its range were saved as unbanded before it existed.
        // A card's band is only recalculated when its price is next synced,
        // which leaves those stuck: batch generation's pool query requires
        // rarity_band IS NOT NULL, so an unbanded card can't be picked at all.
        //
        // Bounds are literals rather than being read from RarityBander so this
        // stays a fixed, one-time correction — a later threshold change
        // shouldn't silently re-band a different set of cards if this ever runs
        // again on a rebuilt database.
        //
        // Deliberately does NOT touch £350.00–£424.99: that sits below chase's
        // floor and above mythic's ceiling, and belongs to no band by design.
        //
        // Only unallocated in-stock cards are updated — every other card has
        // its band frozen once it leaves the available pool, by design
        // (CardInventory::isBandLocked()).
        DB::table('card_inventory')
            ->whereNull('rarity_band')
            ->whereBetween('market_value_pence', [42500, 75000])
            ->where('status', 'in_stock')
            ->whereNull('pack_id')
            ->update(['rarity_band' => 'chase']);
    }

    public function down(): void
    {
        // Intentionally empty. Rows this set are indistinguishable from ones a
        // normal price sync has banded chase since, so a reversal would clear
        // more than it wrote. Unbanding a card is also non-destructive to undo
        // by hand (resync its price, or set the band in the admin panel).
    }
};
