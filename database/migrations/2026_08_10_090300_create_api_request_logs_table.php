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
        // Per-request log of every partner API call — populated by
        // App\Http\Middleware\LogStoreApiRequest, surfaced to admins on
        // StoreResource's "API logs" tab to help review and approve an
        // integration before switching a store to live or enabling
        // markAsSold. Pruned by App\Console\Commands\PruneApiRequestLogsCommand.
        Schema::create('api_request_logs', function (Blueprint $table) {
            $table->id();
            // Nullable: a failed/invalid token means no store was ever resolved,
            // but that request is still worth seeing (e.g. a partner sending the
            // wrong header while setting up their integration).
            $table->foreignId('store_id')->nullable()->constrained()->nullOnDelete();
            $table->string('method', 10);
            $table->string('path');
            $table->unsignedSmallInteger('status_code');
            $table->unsignedInteger('duration_ms');
            $table->string('ip', 45)->nullable();
            $table->json('response_summary')->nullable();
            $table->timestamp('created_at')->nullable();

            $table->index(['store_id', 'created_at']);
            $table->index('status_code');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('api_request_logs');
    }
};
