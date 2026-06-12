<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('batches', function (Blueprint $table) {
            $table->integer('total_cost_pence')->default(0)->nullable()->change();
            $table->integer('total_market_value_pence')->default(0)->nullable()->change();
            $table->integer('sale_price_pence')->default(0)->nullable()->change();
            $table->integer('margin_pence')->default(0)->nullable()->change();
            $table->integer('margin_scheme_vat_pence')->default(0)->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('batches', function (Blueprint $table) {
            // You can tighten back if you really want, but often not needed
        });
    }
};
