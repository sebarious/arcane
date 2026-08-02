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
        Schema::create('customer_sell_submission_items', function (Blueprint $table) {
            $table->id();
            // Custom (short) constraint name — the auto-generated one exceeds MySQL's
            // 64-char identifier limit (Postgres just silently truncates it instead).
            $table->foreignId('customer_sell_submission_id')
                ->constrained(indexName: 'css_items_submission_id_foreign')
                ->cascadeOnDelete();
            $table->string('product_id')->nullable();
            $table->string('card_name');
            $table->string('card_number')->nullable();
            $table->string('set_name')->nullable();
            $table->string('rarity')->nullable();
            $table->string('image_url')->nullable();
            $table->integer('market_value_pence');           // snapshot at submission time
            $table->unsignedInteger('quantity');
            $table->enum('band', ['common', 'rare', 'super', 'legendary', 'mythic'])->nullable();
            $table->decimal('offer_percentage', 4, 2);        // e.g. 0.80
            $table->integer('unit_offer_pence');
            $table->integer('total_offer_pence');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('customer_sell_submission_items');
    }
};
