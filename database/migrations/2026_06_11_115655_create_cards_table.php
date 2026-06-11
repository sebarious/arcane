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
        Schema::create('cards', function (Blueprint $table) {
            $table->id();
            $table->string('name');                       // "Charizard ex"
            $table->string('set_code', 20);               // "sv3pt5"
            $table->string('set_name');                   // "Scarlet & Violet 151"
            $table->string('card_number', 20);            // "199/165"
            $table->string('variant')->nullable();        // "Special Illustration Rare"
            $table->string('language', 5)->default('en');
            // Pokémon's own printed rarity (not our band)
            $table->string('printed_rarity')->nullable();
            // Image URLs (CDN or S3)
            $table->string('image_front')->nullable();
            $table->string('image_back')->nullable();
            // External IDs for price lookups & dedup
            $table->jsonb('external_ids')->default('{}'); // tcgplayer_id, pokemon_tcg_io_id, cardmarket_id, ebay_epid
            $table->timestamps();
            $table->unique(['set_code', 'card_number', 'variant', 'language'], 'cards_unique_print');
            $table->index('name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cards');
    }
};
