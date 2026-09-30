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
        Schema::table('users', function (Blueprint $table) {
            // Digital Rips only — where a "kept" card gets posted to. Nullable:
            // most users on this table are sellers/admins who never touch this.
            // Same field shape as stores' own address_line_1/city/postcode.
            $table->string('shipping_name')->nullable();
            $table->string('shipping_address_line_1')->nullable();
            $table->string('shipping_address_line_2')->nullable();
            $table->string('shipping_city')->nullable();
            $table->string('shipping_postcode')->nullable();
            $table->string('shipping_country', 2)->nullable()->default('GB');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'shipping_name',
                'shipping_address_line_1',
                'shipping_address_line_2',
                'shipping_city',
                'shipping_postcode',
                'shipping_country',
            ]);
        });
    }
};
