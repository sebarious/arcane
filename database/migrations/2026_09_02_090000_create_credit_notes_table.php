<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('credit_notes', function (Blueprint $table) {
            $table->id();
            $table->string('number')->unique();
            $table->foreignId('store_id')->constrained()->restrictOnDelete();

            // Set only once resolved via "apply to invoice" — null while pending,
            // and null forever if instead resolved via "mark as paid".
            $table->foreignId('invoice_id')->nullable()->constrained()->nullOnDelete();

            $table->integer('amount_pence');
            $table->text('reason');

            // issued -> applied (attached to an invoice) | paid (refunded manually).
            // Terminal either way — a resolved credit note is never reopened.
            $table->string('status')->default('issued');

            $table->foreignId('issued_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('paid_by_user_id')->nullable()->constrained('users')->nullOnDelete();

            $table->timestamp('applied_at')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamp('last_emailed_at')->nullable();

            $table->timestamps();

            $table->index(['store_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('credit_notes');
    }
};
