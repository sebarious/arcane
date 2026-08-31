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
        Schema::table('rip_withdrawals', function (Blueprint $table) {
            // amount_pence stays "what the customer receives" — fee_pence is
            // the extra 3% charged on top and debited from the wallet
            // alongside it (see RipWalletService::requestWithdrawal()).
            // Snapshotted at request time, not derived from a config rate
            // live, so a later rate change never rewrites a historical
            // withdrawal's own numbers.
            $table->unsignedInteger('fee_pence')->default(0)->after('amount_pence');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('rip_withdrawals', function (Blueprint $table) {
            $table->dropColumn('fee_pence');
        });
    }
};
