<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('stores', function (Blueprint $table) {
            // Running balance — the source of truth for how much credit a store has
            // right now. store_credit_transactions is the audit trail behind it.
            $table->integer('credit_balance_pence')->default(0)->after('affiliate_code');
        });
    }

    public function down(): void
    {
        Schema::table('stores', function (Blueprint $table) {
            $table->dropColumn('credit_balance_pence');
        });
    }
};
