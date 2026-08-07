<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('batches', function (Blueprint $table) {
            // Provably-fair batch generation: verification_hash = SHA-256(verification_seed),
            // published the moment the batch is created (committed_at) — before generation
            // has run, so the seed can't be chosen after seeing what it would produce. The
            // seed itself stays hidden until generation locks the batch (revealed_at), at
            // which point anyone can hash it themselves and confirm it matches the hash that
            // was published up front. See App\Services\Verification\*.
            $table->string('verification_seed', 64)->nullable()->after('top_card_2_id');
            $table->string('verification_hash', 64)->nullable()->unique()->after('verification_seed');
            $table->timestamp('verification_committed_at')->nullable()->after('verification_hash');
            $table->timestamp('verification_revealed_at')->nullable()->after('verification_committed_at');
            $table->string('verification_snapshot_path')->nullable()->after('verification_revealed_at');
        });
    }

    public function down(): void
    {
        Schema::table('batches', function (Blueprint $table) {
            $table->dropColumn([
                'verification_seed',
                'verification_hash',
                'verification_committed_at',
                'verification_revealed_at',
                'verification_snapshot_path',
            ]);
        });
    }
};
