<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('stores', function (Blueprint $table) {
            $table->text('description')->nullable()->after('name');
            $table->jsonb('platforms')->nullable()->after('description');
            $table->jsonb('social_links')->nullable()->after('platforms');
            $table->string('location')->nullable()->after('social_links');
        });
    }

    public function down(): void
    {
        Schema::table('stores', function (Blueprint $table) {
            $table->dropColumn([
                'description',
                'platforms',
                'social_links',
                'location',
            ]);
        });
    }
};
