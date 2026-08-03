<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('customer_sell_submissions', function (Blueprint $table) {
            $table->string('affiliate_code')->nullable()->after('description');
            $table->foreignId('affiliate_store_id')->nullable()->after('affiliate_code')
                ->constrained('stores')->nullOnDelete();
            $table->integer('affiliate_bonus_pence')->nullable()->after('affiliate_store_id');
        });
    }

    public function down(): void
    {
        Schema::table('customer_sell_submissions', function (Blueprint $table) {
            $table->dropConstrainedForeignId('affiliate_store_id');
            $table->dropColumn(['affiliate_code', 'affiliate_bonus_pence']);
        });
    }
};
