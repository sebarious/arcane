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
        Schema::create('customer_sell_submission_notes', function (Blueprint $table) {
            $table->id();
            // Custom (short) constraint name — the auto-generated one exceeds MySQL's
            // 64-char identifier limit (Postgres just silently truncates it instead).
            $table->foreignId('customer_sell_submission_id')
                ->constrained(indexName: 'css_notes_submission_id_foreign')
                ->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->text('note');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('customer_sell_submission_notes');
    }
};
