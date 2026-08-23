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
            // When true, every PulseAPI price-sync path (CardPriceSyncer,
            // PulseApiPriceProvider, arcane:refresh-prices, arcane:resync-all-cards)
            // skips this row entirely — market_value_pence stays exactly what an
            // admin set it to until they unlock it. The card still participates in
            // batch generation as normal; only price/band syncing is affected.
            $table->boolean('price_locked')->default(false)->after('market_value_updated_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('card_inventory', function (Blueprint $table) {
            $table->dropColumn('price_locked');
        });
    }
};
