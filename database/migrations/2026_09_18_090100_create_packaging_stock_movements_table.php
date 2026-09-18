<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('packaging_stock_movements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('packaging_stock_id')->constrained()->cascadeOnDelete();
            $table->integer('delta');
            $table->integer('balance_after');
            $table->string('reason');
            $table->foreignId('batch_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('created_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('packaging_stock_movements');
    }
};
