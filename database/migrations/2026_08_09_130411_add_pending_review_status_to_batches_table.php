<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Was a fixed enum() — on Postgres that's a plain varchar plus a *named*
        // CHECK constraint (batches_status_check), which Schema::change() alone
        // doesn't drop (confirmed live: the column type changes, but the old
        // constraint object stays attached and keeps rejecting anything not in
        // its original list). Converted to an unconstrained string so adding
        // 'pending_review' — the new step between generation and actually going
        // live, see BatchGenerator::publish() — doesn't need a constraint
        // rebuild, and neither does the next status this ever needs either.
        Schema::table('batches', function (Blueprint $table) {
            $table->string('status')->default('draft')->change();
        });

        if (DB::connection()->getDriverName() === 'pgsql') {
            DB::statement('ALTER TABLE batches DROP CONSTRAINT IF EXISTS batches_status_check');
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // True reversal — the original constraint, without 'pending_review'.
        // Fails as-is if any row is already that status, same as any other
        // constraint tightening; that's correct, not a bug in this migration.
        if (DB::connection()->getDriverName() === 'pgsql') {
            DB::statement(
                "ALTER TABLE batches ADD CONSTRAINT batches_status_check ".
                "CHECK (status IN ('draft', 'committed', 'dispatched', 'completed', 'cancelled'))"
            );

            return;
        }

        Schema::table('batches', function (Blueprint $table) {
            $table->enum('status', [
                'draft',
                'committed',
                'dispatched',
                'completed',
                'cancelled',
            ])->default('draft')->change();
        });
    }
};
