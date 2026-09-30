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
        Schema::table('rip_orders', function (Blueprint $table) {
            // 'card' (Stripe) or 'wallet' (paid instantly from wallet balance,
            // see RipCheckoutService::payFromWallet()) — lets admin/reporting
            // tell the two apart.
            $table->string('payment_method')->default('card')->after('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('rip_orders', function (Blueprint $table) {
            $table->dropColumn('payment_method');
        });
    }
};
