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
        Schema::table('stores', function (Blueprint $table) {
            // 'test' | 'live' — see App\Enums\ApiMode. Gates which batches (real
            // vs is_test) the partner API returns for this store; an admin flips
            // it once an integration has been reviewed — see
            // StoreResource::toggleApiModeAction() and the API logs relation
            // manager it sits next to.
            $table->string('api_mode')->default('test');
        });

        // Grandfather in stores that were already live under the old single-gate
        // (api_access_granted + api_enabled) scheme — without this they'd
        // suddenly only see sandbox data on next deploy.
        DB::table('stores')
            ->where('api_access_granted', true)
            ->where('api_enabled', true)
            ->update(['api_mode' => 'live']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('stores', function (Blueprint $table) {
            $table->dropColumn('api_mode');
        });
    }
};
