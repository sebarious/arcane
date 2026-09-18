<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('packaging_stocks', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->unsignedInteger('quantity_on_hand')->default(0);
            $table->timestamps();
        });

        // Seed the fixed set of physical packaging items up front — one bag
        // per pack regardless of rarity, one insert per rarity band (see
        // config/banding.php for the bands). Starts at 0; real counts go in
        // via the Packaging stock admin page.
        $now = now();
        DB::table('packaging_stocks')->insert(
            collect(['bag', 'insert_common', 'insert_rare', 'insert_super', 'insert_legendary', 'insert_mythic'])
                ->map(fn ($key) => [
                    'key' => $key,
                    'quantity_on_hand' => 0,
                    'created_at' => $now,
                    'updated_at' => $now,
                ])
                ->all()
        );
    }

    public function down(): void
    {
        Schema::dropIfExists('packaging_stocks');
    }
};
