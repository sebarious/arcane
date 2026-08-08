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
        Schema::table('stores', function (Blueprint $table) {
            // Admin-controlled eligibility gate — a seller can only reach the
            // self-service toggle below once an admin has granted this.
            $table->boolean('api_access_granted')->default(false);
            // Seller-controlled — whether the API is actually live, only
            // meaningful once api_access_granted is true.
            $table->boolean('api_enabled')->default(false);
            $table->string('api_token')->nullable()->unique();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('stores', function (Blueprint $table) {
            $table->dropColumn(['api_access_granted', 'api_enabled', 'api_token']);
        });
    }
};
