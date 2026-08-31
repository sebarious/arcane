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
        Schema::create('rip_packs', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            // Falls back to the default Arcane bag art (resources/ts/Assets/
            // Arcane_pack.webp) on the frontend when null.
            $table->string('image_path')->nullable();
            $table->unsignedInteger('price_pence');
            // Game enum values this pack draws from — RipDrawer pools all
            // selected games together per band (not per-game odds).
            $table->jsonb('games');
            // {common, rare, super, legendary, mythic} => float, admin-validated
            // to sum to 1.0. See RipDrawer.
            $table->jsonb('band_odds');
            // e.g. 0.80 — configurable per pack, not a single global rate.
            $table->decimal('buy_back_percentage', 4, 3);
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->timestamps();

            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rip_packs');
    }
};
