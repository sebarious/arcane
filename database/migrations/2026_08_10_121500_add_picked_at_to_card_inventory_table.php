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
        Schema::table('card_inventory', function (Blueprint $table) {
            // Chaos storage: a card is physically stored in its acquisition_lot's box,
            // sorted alphabetically, until this is stamped — see
            // App\Services\Batches\PickingSheetGenerator, which sets it the moment a
            // card is printed on a picking sheet. From then on it no longer counts
            // toward its lot's box population, so later picks (and their alphabetical
            // "position") always reflect what's still physically in the box.
            $table->timestamp('picked_at')->nullable()->after('delisted_by_user_id');
            $table->index(['acquisition_lot', 'picked_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('card_inventory', function (Blueprint $table) {
            $table->dropIndex(['acquisition_lot', 'picked_at']);
            $table->dropColumn('picked_at');
        });
    }
};
