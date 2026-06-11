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
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->string('number')->unique();              // "INV-2026-0001"
            $table->foreignId('store_id')->constrained()->restrictOnDelete();
            $table->foreignId('batch_id')->nullable()->constrained()->nullOnDelete();
            // Under VAT margin scheme: no separate VAT line on the invoice to the store.
            // We still record the internal numbers for our own accounting.
            $table->integer('total_pence');                  // what the store pays (margin-scheme gross)
            $table->integer('internal_cost_pence');          // our cost of goods
            $table->integer('internal_margin_pence');        // sale - cost
            $table->integer('internal_margin_vat_pence');    // margin / 6
            $table->enum('status', ['draft', 'sent', 'paid', 'overdue', 'cancelled'])->default('draft');
            $table->date('issued_on')->nullable();
            $table->date('due_on')->nullable();
            $table->timestamp('paid_at')->nullable();
            // Stripe linkage
            $table->string('stripe_invoice_id')->nullable()->index();
            $table->string('stripe_payment_intent_id')->nullable();
            $table->string('pdf_path')->nullable();
            $table->timestamps();
            $table->index(['store_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invoices');
    }
};
