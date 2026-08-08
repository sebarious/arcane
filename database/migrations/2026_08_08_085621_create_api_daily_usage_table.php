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
        // One row per store per calendar day — incremented atomically by
        // EnforceStoreDailyApiLimit on every seller-API request, both to
        // enforce Store::daily_request_limit and to back the "calls used
        // today" figure and 30-day usage graph on the seller's API page.
        Schema::create('api_daily_usage', function (Blueprint $table) {
            $table->id();
            $table->foreignId('store_id')->constrained()->cascadeOnDelete();
            $table->date('date');
            $table->unsignedInteger('request_count')->default(0);
            $table->timestamps();

            $table->unique(['store_id', 'date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('api_daily_usage');
    }
};
