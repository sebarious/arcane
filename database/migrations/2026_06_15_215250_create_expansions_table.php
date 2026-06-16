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
        Schema::create('expansions', function (Blueprint $table) {
            $table->id();
            $table->string('scrydex_id')->unique();
            $table->string('game')->nullable();
            $table->string('name');
            $table->string('series')->nullable();
            $table->string('code')->nullable();
            $table->integer('total')->nullable();
            $table->integer('printed_total')->nullable();
            $table->string('language')->nullable();
            $table->string('language_code')->nullable();
            $table->date('release_date')->nullable();
            $table->boolean('is_online_only')->default(false);
            $table->string('logo')->nullable();
            $table->string('symbol')->nullable();
            $table->json('translations')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('expansions');
    }
};
