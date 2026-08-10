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
        Schema::table('batches', function (Blueprint $table) {
            // Set once by GeneratePickingSheetJob, mirroring qr_sheet_pdf_path.
            // Never regenerated after the fact — the computed positions on that
            // PDF are a permanent record of what staff were told, and reprinting
            // could recompute against a box that's since shrunk further.
            $table->string('picking_sheet_pdf_path')->nullable()->after('qr_sheet_pdf_path');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('batches', function (Blueprint $table) {
            $table->dropColumn('picking_sheet_pdf_path');
        });
    }
};
