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
            // When every pack in the batch first went sold — stamped by
            // arcane:mark-empty-batches, not computed on every storefront
            // request. Drives the 1-hour grace period before an emptied
            // batch drops off the Card Lists page and its store's profile.
            $table->timestamp('emptied_at')->nullable()->after('dispatched_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('batches', function (Blueprint $table) {
            $table->dropColumn('emptied_at');
        });
    }
};
