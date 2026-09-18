<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Mirrors the seeding in 2026_08_07_111947_create_batch_type_settings_table
        // — every tier starts requestable by default until an admin turns it off.
        // That migration's seeding loop runs over BatchType::cases() as it stands
        // *today*, not as it stood when that migration was first written — so on
        // a fresh install (migrate:fresh, a new environment, tests) it already
        // seeds 'premium' itself now that the case exists, and this insert would
        // collide with it. insertOrIgnore() makes this a no-op there, while still
        // backfilling the row on any database that migrated before this case
        // existed.
        DB::table('batch_type_settings')->insertOrIgnore([
            'type' => 'premium',
            'enabled' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function down(): void
    {
        DB::table('batch_type_settings')->where('type', 'premium')->delete();
    }
};
