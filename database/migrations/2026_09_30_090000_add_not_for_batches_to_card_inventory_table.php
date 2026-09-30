<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('card_inventory', function (Blueprint $table) {
            // Below the condition we're willing to seal into a mystery pack,
            // but still perfectly sellable face-up — kiosk, card wall, eBay.
            // Kept as its own flag rather than a status: the card is genuinely
            // still in stock, it's only batch generation that has to skip it.
            $table->boolean('not_for_batches')->default(false)->after('in_card_wall');
        });
    }

    public function down(): void
    {
        Schema::table('card_inventory', function (Blueprint $table) {
            $table->dropColumn('not_for_batches');
        });
    }
};
