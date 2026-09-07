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
            $table->boolean('on_ebay')->default(false)->after('picked_at');
            $table->boolean('in_card_wall')->default(false)->after('on_ebay');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('card_inventory', function (Blueprint $table) {
            $table->dropColumn(['on_ebay', 'in_card_wall']);
        });
    }
};
