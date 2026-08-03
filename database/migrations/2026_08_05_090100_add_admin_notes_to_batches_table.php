<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('batches', function (Blueprint $table) {
            // Was already being written to on batch requests, but the column never
            // existed — Eloquent's mass-assignment guard was silently dropping it.
            $table->text('admin_notes')->nullable()->after('failure_reason');
        });
    }

    public function down(): void
    {
        Schema::table('batches', function (Blueprint $table) {
            $table->dropColumn('admin_notes');
        });
    }
};
