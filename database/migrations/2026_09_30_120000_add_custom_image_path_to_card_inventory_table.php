<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('card_inventory', function (Blueprint $table) {
            // Our own photograph of this physical card, for stock PulseAPI's
            // stock art doesn't represent — a graded slab above all, where the
            // label and case are most of what the buyer is paying for.
            //
            // Kept separate from image_url rather than overwriting it, so the
            // PulseAPI artwork survives a re-sync and can be fallen back on.
            // CardInventory::getImageUrlAttribute() prefers this when set.
            $table->string('custom_image_path')->nullable()->after('image_url');
        });
    }

    public function down(): void
    {
        Schema::table('card_inventory', function (Blueprint $table) {
            $table->dropColumn('custom_image_path');
        });
    }
};
