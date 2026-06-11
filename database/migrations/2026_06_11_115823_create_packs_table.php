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
        Schema::create('packs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('batch_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('sequence_no');          // 1..250 within the batch
            $table->enum('status', ['sealed', 'sold', 'voided'])->default('sealed');
            $table->timestamp('sold_at')->nullable();
            $table->timestamps();
            $table->unique(['batch_id', 'sequence_no']);
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('packs');
    }
};
