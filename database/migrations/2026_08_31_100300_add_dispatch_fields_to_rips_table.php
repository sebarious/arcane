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
        Schema::table('rips', function (Blueprint $table) {
            // Only ever set on a 'kept' decision — a kept card has to be
            // physically posted to the customer, unlike a sold-back one
            // (money only) or an unopened/undecided one (nothing to post yet).
            $table->timestamp('dispatched_at')->nullable()->after('decided_at');
            $table->foreignId('dispatched_by_user_id')->nullable()->after('dispatched_at')
                ->constrained('users')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('rips', function (Blueprint $table) {
            $table->dropConstrainedForeignId('dispatched_by_user_id');
            $table->dropColumn('dispatched_at');
        });
    }
};
