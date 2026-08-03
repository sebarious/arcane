<?php

use App\Models\Store;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('stores', function (Blueprint $table) {
            $table->string('affiliate_code')->nullable()->unique()->after('slug');
        });

        // Backfill existing stores — new ones get a code automatically via the model.
        Store::whereNull('affiliate_code')->each(function (Store $store) {
            $store->update(['affiliate_code' => Store::generateAffiliateCode($store->name ?: $store->slug)]);
        });
    }

    public function down(): void
    {
        Schema::table('stores', function (Blueprint $table) {
            $table->dropColumn('affiliate_code');
        });
    }
};
