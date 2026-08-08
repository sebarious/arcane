<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Singleton table — a single row (id 1) holding the seller-API-wide
        // rate/quota knobs, editable from Filament instead of a config file
        // deploy. See App\Models\ApiSetting::current().
        Schema::create('api_settings', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('rate_limit_per_minute')->default(20);
            $table->unsignedInteger('daily_request_limit')->default(1000);
            $table->timestamps();
        });

        DB::table('api_settings')->insert([
            'id' => 1,
            'rate_limit_per_minute' => 20,
            'daily_request_limit' => 1000,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('api_settings');
    }
};
