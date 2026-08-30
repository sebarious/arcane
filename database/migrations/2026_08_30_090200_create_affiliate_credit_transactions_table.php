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
        Schema::create('affiliate_credit_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('affiliate_id')->constrained()->cascadeOnDelete();
            $table->enum('type', ['credit', 'redemption']);
            // Signed — positive for credit added, negative for withdrawal redemptions.
            $table->integer('amount_pence');
            // Running balance snapshot right after this entry, for a quick audit trail
            // without having to re-sum the whole ledger.
            $table->integer('balance_after_pence');
            $table->string('reason')->nullable();
            $table->foreignId('customer_sell_submission_id')->nullable()
                ->constrained('customer_sell_submissions')->nullOnDelete();
            // Set on the 'redemption' row a withdrawal request creates (and on the
            // refunding 'credit' row if that withdrawal is later rejected) — see
            // AffiliateCreditService.
            $table->foreignId('affiliate_withdrawal_id')->nullable()
                ->constrained('affiliate_withdrawals')->nullOnDelete();
            $table->foreignId('created_by_user_id')->nullable()
                ->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->index(['affiliate_id', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('affiliate_credit_transactions');
    }
};
