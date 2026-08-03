<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('store_credit_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('store_id')->constrained()->cascadeOnDelete();
            $table->enum('type', ['credit', 'redemption', 'adjustment']);
            // Signed — positive for credit added, negative for redemptions/deductions.
            $table->integer('amount_pence');
            // Running balance snapshot right after this entry, for a quick audit trail
            // without having to re-sum the whole ledger.
            $table->integer('balance_after_pence');
            $table->string('reason')->nullable();
            $table->foreignId('customer_sell_submission_id')->nullable()
                ->constrained('customer_sell_submissions')->nullOnDelete();
            $table->foreignId('invoice_id')->nullable()
                ->constrained('invoices')->nullOnDelete();
            $table->foreignId('created_by_user_id')->nullable()
                ->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->index(['store_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('store_credit_transactions');
    }
};
