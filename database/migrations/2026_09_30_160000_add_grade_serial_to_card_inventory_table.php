<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('card_inventory', function (Blueprint $table) {
            // The number printed on the slab label — PSA and CGC call it a
            // certification number, Beckett a serial number. It's what ties
            // this physical slab to the grader's own register, so it's the
            // one field that proves a specific card is the one we listed.
            //
            // Nullable and unconstrained on purpose: PulseAPI populates
            // graded_by/grade without ever supplying this, so requiring it
            // would make those records uneditable.
            $table->string('grade_serial', 40)->nullable()->after('grade');
            $table->index('grade_serial');
        });
    }

    public function down(): void
    {
        Schema::table('card_inventory', function (Blueprint $table) {
            $table->dropIndex(['grade_serial']);
            $table->dropColumn('grade_serial');
        });
    }
};
