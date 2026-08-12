<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('card_inventory', function (Blueprint $table) {
            // The Laravel session ID that placed the current reserved_until hold —
            // without this, if a hold silently expires and a *different* kiosk
            // session grabs the card, the original session's checkout would have
            // no way to notice it no longer actually holds it (reserved_until alone
            // being in the future doesn't say whose hold it is). See
            // App\Services\Kiosk\KioskBasketService.
            $table->string('reserved_by')->nullable()->after('reserved_until');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('card_inventory', function (Blueprint $table) {
            $table->dropColumn('reserved_by');
        });
    }
};
