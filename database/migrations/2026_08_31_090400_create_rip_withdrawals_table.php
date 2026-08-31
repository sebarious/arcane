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
        Schema::create('rip_withdrawals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('rip_wallet_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('amount_pence');
            $table->enum('status', ['pending', 'paid', 'rejected'])->default('pending');
            // Snapshotted from the wallet's bank details at request time — a
            // later change must never make a historical (especially already-paid)
            // withdrawal ambiguous about which account it actually went to.
            $table->string('bank_account_name');
            $table->string('bank_sort_code');
            $table->string('bank_account_number');
            $table->timestamp('paid_at')->nullable();
            $table->foreignId('paid_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->text('admin_notes')->nullable();
            $table->timestamps();

            $table->index(['rip_wallet_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rip_withdrawals');
    }
};
