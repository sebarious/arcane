<?php

use App\Enums\BatchType;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('batch_type_settings', function (Blueprint $table) {
            $table->id();
            $table->string('type')->unique();
            $table->boolean('enabled')->default(true);
            $table->timestamps();
        });

        // Seed a row per BatchType up front — every tier stays requestable by
        // default until an admin explicitly turns one off.
        $now = now();
        foreach (BatchType::cases() as $type) {
            DB::table('batch_type_settings')->insert([
                'type' => $type->value,
                'enabled' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('batch_type_settings');
    }
};
