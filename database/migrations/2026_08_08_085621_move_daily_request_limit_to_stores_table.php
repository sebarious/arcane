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
            // Per-store now, not a single global cap — see App\Filament\Resources\Stores\StoreResource.
            $table->unsignedInteger('daily_request_limit')->default(1000);
        });

        Schema::table('api_settings', function (Blueprint $table) {
            $table->dropColumn('daily_request_limit');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('api_settings', function (Blueprint $table) {
            $table->unsignedInteger('daily_request_limit')->default(1000);
        });

        Schema::table('stores', function (Blueprint $table) {
            $table->dropColumn('daily_request_limit');
        });
    }
};
