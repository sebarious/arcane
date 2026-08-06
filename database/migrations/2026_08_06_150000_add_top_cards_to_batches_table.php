<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('batches', function (Blueprint $table) {
            // Snapshot of the two highest market_value_pence cards selected into this
            // batch at generation time — see BatchGenerator::generate(). Frozen on
            // purpose: the "Card Lists" storefront thumbnail must stay the same even
            // after these specific cards are pulled/sold out of the batch, so this is
            // never recomputed after generation.
            $table->foreignId('top_card_1_id')->nullable()->after('game')
                ->constrained('card_inventory')->nullOnDelete();
            $table->foreignId('top_card_2_id')->nullable()->after('top_card_1_id')
                ->constrained('card_inventory')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('batches', function (Blueprint $table) {
            $table->dropConstrainedForeignId('top_card_1_id');
            $table->dropConstrainedForeignId('top_card_2_id');
        });
    }
};
