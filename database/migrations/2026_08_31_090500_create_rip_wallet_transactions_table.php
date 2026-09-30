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
        Schema::create('rip_wallet_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('rip_wallet_id')->constrained()->cascadeOnDelete();
            $table->enum('type', ['credit', 'redemption']);
            // Signed — positive for credit added (a sell-back), negative for
            // withdrawal redemptions.
            $table->integer('amount_pence');
            $table->integer('balance_after_pence');
            $table->string('reason')->nullable();
            $table->foreignId('rip_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('rip_withdrawal_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('created_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['rip_wallet_id', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rip_wallet_transactions');
    }
};
