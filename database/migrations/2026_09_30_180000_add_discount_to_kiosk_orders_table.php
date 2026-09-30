<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('kiosk_orders', function (Blueprint $table) {
            // total_pence stays what was actually charged. These record how it
            // got there, so a discounted sale can be explained months later
            // without reverse-engineering it from the line items.
            $table->unsignedInteger('subtotal_pence')->default(0)->after('total_pence');
            $table->string('discount_type')->nullable()->after('subtotal_pence');
            // Percent (0-100) or pence off, depending on discount_type.
            $table->unsignedInteger('discount_value')->nullable()->after('discount_type');
            $table->unsignedInteger('discount_pence')->default(0)->after('discount_value');
        });
    }

    public function down(): void
    {
        Schema::table('kiosk_orders', function (Blueprint $table) {
            $table->dropColumn(['subtotal_pence', 'discount_type', 'discount_value', 'discount_pence']);
        });
    }
};
