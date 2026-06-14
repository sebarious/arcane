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
            if (! Schema::hasColumn('customer_sell_submissions', 'reference')) {
                $table->string('reference')->unique()->after('id');
            }
            $table->string('customer_name')->nullable()->change();
            $table->string('customer_email')->nullable()->change();
            $table->string('customer_phone')->nullable()->change();
            $table->string('customer_postcode')->nullable()->change();
            $table->jsonb('images')->default('[]')->change();
            $table->string('status', 32)->default('submitted')->nullable(false)->change();
            $table->integer('estimated_value_pence')->nullable()->change();
            $table->integer('offered_value_pence')->nullable()->change();
            if (! Schema::hasColumn('customer_sell_submissions', 'offer_expires_on')) {
                $table->date('offer_expires_on')->nullable();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
