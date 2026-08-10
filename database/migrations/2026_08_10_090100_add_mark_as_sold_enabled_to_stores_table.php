<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('stores', function (Blueprint $table) {
            // Separate, admin-only approval gate for the markAsSold endpoint — a
            // store can already be reading batch data via the API while this
            // stays off, until an admin has reviewed their integration.
            $table->boolean('mark_as_sold_enabled')->default(false);
        });

        // Grandfather in stores already relying on markAsSold in production.
        DB::table('stores')
            ->where('api_access_granted', true)
            ->where('api_enabled', true)
            ->update(['mark_as_sold_enabled' => true]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('stores', function (Blueprint $table) {
            $table->dropColumn('mark_as_sold_enabled');
        });
    }
};
