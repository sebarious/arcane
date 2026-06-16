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
        Schema::create('customer_sell_submissions', function (Blueprint $table) {
            $table->id();
            $table->string('reference')->unique();            // "SELL-2026-0001"
            $table->string('customer_name');
            $table->string('customer_email');
            $table->string('customer_phone')->nullable();
            $table->string('customer_postcode')->nullable();
            $table->jsonb('images');           // array of S3 paths
            $table->text('description')->nullable();
            $table->enum('status', [
                'submitted',
                'under_review',
                'offer_made',
                'accepted',
                'declined',
                'completed',
                'withdrawn',
            ])->default('submitted');
            $table->integer('estimated_value_pence')->nullable();   // admin's working estimate
            $table->integer('offered_value_pence')->nullable();     // what we offered the customer
            $table->text('admin_notes')->nullable();
            $table->text('decline_reason')->nullable();
            $table->foreignId('reviewed_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamp('offered_at')->nullable();
            $table->timestamp('responded_at')->nullable();
            $table->timestamps();
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('customer_sell_submissions');
    }
};
