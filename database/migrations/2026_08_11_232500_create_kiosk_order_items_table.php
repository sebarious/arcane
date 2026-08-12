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
        Schema::create('kiosk_order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('kiosk_order_id')->constrained()->cascadeOnDelete();
            // Nullable + nullOnDelete: the order/item is the permanent sale record,
            // so it must survive even if the underlying inventory row is ever removed.
            $table->foreignId('card_inventory_id')->nullable()->constrained('card_inventory')->nullOnDelete();
            // Snapshotted at checkout time — a card's own data can change later
            // (price resync, correction) without altering what was actually sold.
            $table->string('card_name');
            $table->string('set_name')->nullable();
            $table->string('card_number')->nullable();
            $table->string('rarity')->nullable();
            $table->unsignedInteger('market_value_pence')->nullable();
            $table->unsignedInteger('unit_price_pence');
            // Lot + position: filled in at payment-confirmation time by the same
            // chaos-storage math as batch picking sheets (see
            // App\Services\Batches\PickingSheetGenerator::pickTargets()). Null
            // until then.
            $table->string('lot')->nullable();
            $table->unsignedInteger('position')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kiosk_order_items');
    }
};
