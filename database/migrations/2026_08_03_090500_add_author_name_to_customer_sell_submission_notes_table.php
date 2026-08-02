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
        Schema::table('customer_sell_submission_notes', function (Blueprint $table) {
            // Snapshot at write time — keeps the history readable even if the
            // user is later renamed/deleted, and avoids relying on a live
            // relationship lookup when rendering the notes list in Filament.
            $table->string('author_name')->nullable()->after('user_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('customer_sell_submission_notes', function (Blueprint $table) {
            $table->dropColumn('author_name');
        });
    }
};
