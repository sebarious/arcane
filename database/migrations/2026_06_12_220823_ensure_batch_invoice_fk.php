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
        Schema::table('batches', function (Blueprint $table) {
            if (! Schema::hasColumn('batches', 'invoice_id')) {
                $table->unsignedBigInteger('invoice_id')->nullable();
            }
            $table->foreign('invoice_id')
                ->references('id')->on('invoices')
                ->nullOnDelete();
        });
    }
    public function down(): void
    {
        Schema::table('batches', function (Blueprint $table) {
            $table->dropForeign(['invoice_id']);
        });
    }
};
