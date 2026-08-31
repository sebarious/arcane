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
        Schema::table('customer_sell_submissions', function (Blueprint $table) {
            // A submission's affiliate code resolves to exactly one of
            // affiliate_store_id or affiliate_id, never both — see
            // SubmissionStoreController.
            $table->foreignId('affiliate_id')->nullable()->after('affiliate_store_id')
                ->constrained('affiliates')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('customer_sell_submissions', function (Blueprint $table) {
            $table->dropConstrainedForeignId('affiliate_id');
        });
    }
};
