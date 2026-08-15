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
            // The /test-batch demo Diamond batch's card selection — decoupled from any
            // live Pack/CardInventory FK (see TestBatchService::refresh()), so browsing
            // it never allocates or reserves real stock. Null means "never generated
            // yet", distinct from a real-but-empty snapshot.
            $table->json('demo_snapshot')->nullable()->after('verification_snapshot_path');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('batches', function (Blueprint $table) {
            $table->dropColumn('demo_snapshot');
        });
    }
};
