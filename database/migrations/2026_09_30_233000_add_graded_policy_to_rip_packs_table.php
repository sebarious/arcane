<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('rip_packs', function (Blueprint $table) {
            // Deliberately a plain string rather than enum(): on MySQL an
            // enum() column is a real ENUM, so adding a fourth policy later
            // means an ALTER on a live table, and enum()->change() emits SQL
            // Postgres rejects outright. Learned the hard way when the chase
            // band was added — see 2026_09_18_160000_add_chase_to_rarity_band_enum.
            $table->string('graded_policy', 20)
                ->default('exclude')
                ->after('band_odds');
        });
    }

    public function down(): void
    {
        Schema::table('rip_packs', function (Blueprint $table) {
            $table->dropColumn('graded_policy');
        });
    }
};
