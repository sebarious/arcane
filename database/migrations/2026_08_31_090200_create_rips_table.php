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
        Schema::create('rips', function (Blueprint $table) {
            $table->id();
            $table->foreignId('rip_order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('rip_pack_id')->nullable()->constrained()->nullOnDelete();
            // Snapshotted at purchase — a later RipPack edit (price, buy-back %)
            // must never change what an already-sold rip promised. Mirrors
            // KioskOrderItem's card-detail snapshot for the same reason.
            $table->string('pack_name');
            $table->unsignedInteger('price_pence');
            $table->decimal('buy_back_percentage', 4, 3);

            // Provably-fair commit/reveal — same shape as Batch's verification_*
            // columns (see 2026_08_07_121534_add_verification_fields_to_batches_table).
            // Committed at payment confirmation, before the customer ever opens it.
            $table->string('verification_seed', 64)->nullable();
            $table->string('verification_hash', 64)->nullable()->unique();
            $table->timestamp('verification_committed_at')->nullable();
            $table->string('verification_snapshot_path')->nullable();

            // Nullable + nullOnDelete: the rip is the permanent sale/draw record,
            // must survive even if the underlying inventory row is ever removed.
            $table->foreignId('card_inventory_id')->nullable()->constrained('card_inventory')->nullOnDelete();

            // Set only when the customer actually triggers the tear-open
            // animation — the card is already determined (drawn above) by
            // then, but the API must withhold its details until this is set.
            $table->timestamp('opened_at')->nullable();

            $table->enum('decision', ['kept', 'sold_back'])->nullable();
            $table->timestamp('decided_at')->nullable();
            $table->unsignedInteger('sold_back_pence')->nullable();

            $table->timestamps();

            $table->index(['rip_order_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rips');
    }
};
