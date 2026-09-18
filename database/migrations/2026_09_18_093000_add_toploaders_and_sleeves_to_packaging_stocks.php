<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Unlike bags/inserts, these aren't deducted by BatchGenerator — they
        // get used up (and adjusted) manually on a regular basis, so they
        // just need a row each to track on-hand counts against.
        $now = now();
        DB::table('packaging_stocks')->insert(
            collect(['toploader', 'sleeve'])
                ->map(fn ($key) => [
                    'key' => $key,
                    'quantity_on_hand' => 0,
                    'created_at' => $now,
                    'updated_at' => $now,
                ])
                ->all()
        );
    }

    public function down(): void
    {
        DB::table('packaging_stocks')->whereIn('key', ['toploader', 'sleeve'])->delete();
    }
};
