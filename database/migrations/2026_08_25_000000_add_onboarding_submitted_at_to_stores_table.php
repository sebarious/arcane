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
            // Set once a seller submits the in-app onboarding form (bio, location,
            // platforms, social links, logo) — null means they haven't gotten there
            // yet. Combined with public_page_enabled this fully describes onboarding
            // state (not started / submitted, awaiting review / live), so no separate
            // status enum is needed.
            $table->timestamp('onboarding_submitted_at')->nullable()->after('public_page_enabled');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('stores', function (Blueprint $table) {
            $table->dropColumn('onboarding_submitted_at');
        });
    }
};
