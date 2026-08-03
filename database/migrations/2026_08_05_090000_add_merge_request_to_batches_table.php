<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('batches', function (Blueprint $table) {
            // Set by the seller when requesting a new batch — an existing (depleted)
            // batch of theirs whose remaining packs should be merged into this new one
            // once it's generated. Purely informational for staff; the actual merge
            // still happens manually via BatchMerger, same as an unrequested merge.
            $table->foreignId('merge_request_batch_id')->nullable()->after('merged_at')
                ->constrained('batches')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('batches', function (Blueprint $table) {
            $table->dropConstrainedForeignId('merge_request_batch_id');
        });
    }
};
