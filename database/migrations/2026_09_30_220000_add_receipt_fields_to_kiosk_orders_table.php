<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('kiosk_orders', function (Blueprint $table) {
            // Kept so a receipt can be sent again later without asking the
            // customer for their address a second time — the usual reason
            // being that it never arrived.
            $table->string('customer_email')->nullable()->after('stripe_payment_intent_id');
            $table->timestamp('receipt_sent_at')->nullable()->after('customer_email');
        });
    }

    public function down(): void
    {
        Schema::table('kiosk_orders', function (Blueprint $table) {
            $table->dropColumn(['customer_email', 'receipt_sent_at']);
        });
    }
};
