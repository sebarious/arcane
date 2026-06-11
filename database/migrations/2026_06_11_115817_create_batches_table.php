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
        Schema::create('batches', function (Blueprint $table) {
            $table->id();
            $table->string('reference')->unique();           // "ARC-2026-0001"
            $table->foreignId('store_id')->nullable()->constrained()->nullOnDelete();
            $table->enum('status', [
                'draft',         // generated, awaiting admin review
                'committed',     // confirmed, cards allocated, invoice raised
                'dispatched',    // sent to store
                'completed',     // all packs sold
                'cancelled',
            ])->default('draft');
            // 250 packs per batch
            $table->unsignedInteger('pack_count')->default(250);
            // Money — all pence, all margin-scheme aware
            $table->integer('total_cost_pence');             // sum of card_inventory.cost_pence
            $table->integer('total_market_value_pence');     // sum of card_inventory.market_value_pence at commit
            $table->integer('sale_price_pence');             // £2,237.50 = 223750
            $table->integer('margin_pence');                 // sale - cost
            $table->integer('margin_scheme_vat_pence');      // margin / 6, rounded
            $table->unsignedBigInteger('invoice_id')->nullable();
            $table->string('qr_sheet_pdf_path')->nullable();
            $table->timestamp('dispatched_at')->nullable();
            $table->timestamp('committed_at')->nullable();
            $table->timestamps();
            $table->index(['store_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('batches');
    }
};
