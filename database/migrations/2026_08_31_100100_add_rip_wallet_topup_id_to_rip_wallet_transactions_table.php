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
        Schema::table('rip_wallet_transactions', function (Blueprint $table) {
            // Same nullable-FK-per-source convention as rip_id/rip_withdrawal_id —
            // set only on a credit that came from a customer topping up their
            // own wallet via card, rather than a sell-back.
            $table->foreignId('rip_wallet_topup_id')->nullable()->after('rip_withdrawal_id')
                ->constrained()->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('rip_wallet_transactions', function (Blueprint $table) {
            $table->dropConstrainedForeignId('rip_wallet_topup_id');
        });
    }
};
