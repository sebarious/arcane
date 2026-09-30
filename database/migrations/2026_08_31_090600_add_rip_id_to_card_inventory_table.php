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
            // A card is committed to exactly one of a physical Pack or a
            // digital Rip, never both — same convention as
            // CustomerSellSubmission's affiliate_store_id/affiliate_id.
            $table->foreignId('rip_id')->nullable()->after('pack_id')
                ->constrained()->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('card_inventory', function (Blueprint $table) {
            $table->dropConstrainedForeignId('rip_id');
        });
    }
};
