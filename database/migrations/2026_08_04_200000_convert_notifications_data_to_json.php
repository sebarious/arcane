<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * `data` was created as plain text, but Filament's database notifications panel
     * queries it with the ->>'format' JSON operator — which errors on a non-JSON
     * column type. Existing rows are always valid JSON already (Laravel/Filament
     * only ever write JSON into this column), so this is a safe in-place type change.
     */
    public function up(): void
    {
        match (DB::connection()->getDriverName()) {
            'pgsql' => DB::statement('ALTER TABLE notifications ALTER COLUMN data TYPE jsonb USING data::jsonb'),
            'mysql' => DB::statement('ALTER TABLE notifications MODIFY data JSON NOT NULL'),
            default => Schema::table('notifications', fn (Blueprint $table) => $table->json('data')->change()),
        };
    }

    public function down(): void
    {
        match (DB::connection()->getDriverName()) {
            'pgsql' => DB::statement('ALTER TABLE notifications ALTER COLUMN data TYPE text USING data::text'),
            'mysql' => DB::statement('ALTER TABLE notifications MODIFY data TEXT NOT NULL'),
            default => Schema::table('notifications', fn (Blueprint $table) => $table->text('data')->change()),
        };
    }
};
