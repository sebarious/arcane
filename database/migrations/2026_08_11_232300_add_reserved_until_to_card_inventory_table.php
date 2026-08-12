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
            // A timed hold placed the moment a card is added to a kiosk basket
            // (App\Services\Kiosk\KioskBasketService) — excludes it from other
            // kiosk baskets AND from BatchGenerator's candidate pool until it
            // expires or the sale completes. Never set for any other reason.
            $table->timestamp('reserved_until')->nullable()->after('status');
            $table->index(['status', 'reserved_until']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('card_inventory', function (Blueprint $table) {
            $table->dropIndex(['status', 'reserved_until']);
            $table->dropColumn('reserved_until');
        });
    }
};
