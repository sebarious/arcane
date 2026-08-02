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
        // Drops its FK to cards first.
        Schema::dropIfExists('market_price_snapshots');

        Schema::table('card_inventory', function (Blueprint $table) {
            $table->dropForeign(['card_id']);
            $table->dropColumn('card_id');

            // Denormalized PulseAPI card data — card_inventory is now the catalogue.
            $table->string('product_id')->nullable()->after('id');
            $table->string('card_name')->nullable();
            $table->string('card_number', 20)->nullable();
            $table->string('set_id', 20)->nullable();
            $table->string('set_name')->nullable();
            $table->string('series')->nullable();
            $table->date('release_date')->nullable();
            $table->string('material')->nullable();
            $table->string('promo_info')->nullable();
            $table->string('graded_by', 20)->nullable();
            $table->string('grade', 10)->nullable();
            $table->string('rarity')->nullable();
            $table->integer('rarity_rank')->nullable();
            $table->string('language')->nullable();
            $table->string('illustrator')->nullable();
            $table->integer('pokedex_number')->nullable();
            $table->string('image_url')->nullable();
            $table->string('slug')->nullable();
            $table->timestamp('synced_at')->nullable();

            $table->index('product_id');
        });

        Schema::dropIfExists('cards');
        Schema::dropIfExists('expansions');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        throw new \RuntimeException(
            'This migration drops the cards/expansions/market_price_snapshots tables and cannot be reversed. Restore from a backup if needed.'
        );
    }
};
