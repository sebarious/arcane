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
            // Sandbox fixture batches for stores in ApiMode::Test (see
            // App\Services\Api\SandboxBatchProvisioner). Excluded from the app
            // everywhere else by Batch's default global scope (see
            // App\Models\Batch::booted()), so test data never reaches real
            // reporting, exports, invoicing, or the storefront.
            $table->boolean('is_test')->default(false)->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('batches', function (Blueprint $table) {
            $table->dropColumn('is_test');
        });
    }
};
