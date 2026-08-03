<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            // How much store credit was automatically applied at generation time —
            // total_pence stays the true invoice value; this is a payment offset.
            $table->integer('credit_applied_pence')->default(0)->after('total_pence');
        });
    }

    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->dropColumn('credit_applied_pence');
        });
    }
};
