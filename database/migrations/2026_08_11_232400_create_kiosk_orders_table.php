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
        Schema::create('kiosk_orders', function (Blueprint $table) {
            $table->id();
            $table->string('reference')->unique(); // "KIOSK-2026-0001"
            // pending_payment -> paid -> (fulfilled_at set) | expired | cancelled.
            $table->string('status')->default('pending_payment');
            $table->unsignedInteger('total_pence')->default(0);
            $table->string('stripe_payment_intent_id')->nullable()->unique();
            $table->timestamp('paid_at')->nullable();
            // Staff check-off once the cards have actually been walked over —
            // separate from paid_at, which is purely a payment-status fact.
            $table->timestamp('fulfilled_at')->nullable();
            $table->timestamps();

            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kiosk_orders');
    }
};
