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
        Schema::create('card_inventory', function (Blueprint $table) {
            $table->id();
            $table->foreignId('card_id')->constrained()->restrictOnDelete();
            // Acquisition
            $table->string('condition', 5)->default('NM');         // NM for now; reserved for future
            $table->integer('cost_pence');                          // what WE paid (margin scheme purchase price)
            $table->date('acquired_at');
            $table->string('acquired_from')->nullable();            // "Cardiff Card Show 2026-05", supplier name
            $table->string('acquisition_lot')->nullable();          // group purchases together for margin-scheme stock book
            // Valuation (refreshed periodically)
            $table->integer('market_value_pence')->nullable();
            $table->timestamp('market_value_updated_at')->nullable();
            // Banding (computed from market_value_pence at batch-generation time)
            $table->enum('rarity_band', ['common', 'rare', 'super', 'legendary', 'mythic'])->nullable();
            // Pack / batch linkage (one card per pack)
            $table->unsignedBigInteger('pack_id')->nullable();
            // QR code for in-store delisting
            $table->string('qr_token', 16)->nullable()->unique();
            // Lifecycle
            $table->enum('status', [
                'in_stock',      // sitting in our warehouse
                'allocated',     // assigned to a pack/batch, awaiting dispatch
                'dispatched',    // in store, sealed in pack
                'sold',          // QR scanned at store
                'returned',      // (we said no returns but keep the option)
                'written_off',   // lost, damaged
            ])->default('in_stock');
            // Sale-side accounting (set at batch commit, for margin scheme)
            $table->integer('allocated_sale_price_pence')->nullable();  // pro-rata share of batch sale price
            $table->integer('margin_pence')->nullable();                // sale - cost, for this card
            $table->timestamp('delisted_at')->nullable();
            $table->foreignId('delisted_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->index(['status', 'rarity_band']);
            $table->index('pack_id');
            $table->index('qr_token');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('card_inventory');
    }
};
