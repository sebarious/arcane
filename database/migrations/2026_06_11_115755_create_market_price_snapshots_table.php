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
        Schema::create('market_price_snapshots', function (Blueprint $table) {
            $table->id();
            $table->foreignId('card_id')->constrained()->cascadeOnDelete();
            $table->string('condition', 5)->default('NM');
            $table->string('source', 30);                   // 'ebay_uk', 'cardmarket', 'manual'
            $table->string('currency', 3)->default('GBP');
            $table->integer('median_pence')->nullable();
            $table->integer('mean_pence')->nullable();
            $table->integer('low_pence')->nullable();
            $table->integer('high_pence')->nullable();
            $table->integer('sample_size')->nullable();
            $table->jsonb('raw_payload')->nullable();       // full API response for audit
            $table->timestamp('fetched_at');
            $table->timestamps();
            $table->index(['card_id', 'source', 'fetched_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('market_price_snapshots');
    }
};
