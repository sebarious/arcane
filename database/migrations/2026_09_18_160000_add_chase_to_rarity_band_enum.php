<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * rarity_band was created as an enum of the five original bands
     * (2026_06_11_115737_create_card_inventory_table), which the new 'chase'
     * band isn't a member of. How that bites depends entirely on the driver —
     * which is why it reached a deploy before anyone saw it:
     *
     *   - MySQL (production): a real ENUM column. Writing 'chase' fails with
     *     "SQLSTATE[01000]: Warning: 1265 Data truncated for column
     *     'rarity_band'". This is what broke the Forge deploy.
     *   - Postgres: varchar plus a named card_inventory_rarity_band_check
     *     constraint, so a freshly built database rejects 'chase' too. (A
     *     long-lived dev database that lost the constraint along the way will
     *     happily accept it — hence "works on my machine".)
     *   - SQLite (tests): plain varchar, no constraint at all — accepts
     *     anything, which is why the suite never caught this either.
     *
     * Converted to an unconstrained string rather than widened to a six-value
     * enum, following what 2026_08_09_130411_add_pending_review_status_to_
     * batches_table did for batches.status for exactly this reason: the band
     * list already lives in RarityBander::DEFAULT_THRESHOLDS, and duplicating
     * it in the schema means every new band needs a constraint rebuild — which
     * is the trap that cost this deploy. The next band won't need a migration.
     */
    public function up(): void
    {
        Schema::table('card_inventory', function (Blueprint $table) {
            $table->string('rarity_band', 20)->nullable()->change();
        });

        // change() swaps the column type but leaves the old named CHECK
        // attached on Postgres, still rejecting its original list.
        if (DB::connection()->getDriverName() === 'pgsql') {
            DB::statement('ALTER TABLE card_inventory DROP CONSTRAINT IF EXISTS card_inventory_rarity_band_check');
        }
    }

    public function down(): void
    {
        // The original list has no 'chase', so those rows have to go back to
        // unbanded before it can be enforced again.
        DB::table('card_inventory')->where('rarity_band', 'chase')->update(['rarity_band' => null]);

        if (DB::connection()->getDriverName() === 'pgsql') {
            DB::statement(
                'ALTER TABLE card_inventory ADD CONSTRAINT card_inventory_rarity_band_check '.
                "CHECK (rarity_band IN ('common', 'rare', 'super', 'legendary', 'mythic'))"
            );

            return;
        }

        Schema::table('card_inventory', function (Blueprint $table) {
            $table->enum('rarity_band', ['common', 'rare', 'super', 'legendary', 'mythic'])
                ->nullable()
                ->change();
        });
    }
};
