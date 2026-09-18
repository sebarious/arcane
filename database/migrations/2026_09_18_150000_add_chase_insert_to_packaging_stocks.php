<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // The Premium batch type introduces a new 'chase' rarity_band (see
        // config/banding.php) — BatchGenerator deducts one insert per card
        // in every band it allocates, so this row has to exist before any
        // Premium batch can be generated.
        DB::table('packaging_stocks')->insert([
            'key' => 'insert_chase',
            'quantity_on_hand' => 0,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function down(): void
    {
        DB::table('packaging_stocks')->where('key', 'insert_chase')->delete();
    }
};
