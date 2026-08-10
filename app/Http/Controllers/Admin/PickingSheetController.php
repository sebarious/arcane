<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Batch;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class PickingSheetController extends Controller
{
    /**
     * Streams the most recently generated picking sheet. Unlike the QR sheet,
     * this never generates on visit — generation mutates inventory (stamps
     * picked_at), so it only ever happens via the explicit "Generate picking
     * sheet" admin action (see BatchResource::generatePickingSheetAction()).
     */
    public function __invoke(Request $request, Batch $batch)
    {
        if (! $request->user()?->hasRole('admin')) {
            abort(403);
        }

        if (! $batch->picking_sheet_pdf_path || ! Storage::disk('local')->exists($batch->picking_sheet_pdf_path)) {
            abort(404, 'No picking sheet has been generated for this batch yet.');
        }

        return response()->streamDownload(
            fn () => print Storage::disk('local')->get($batch->picking_sheet_pdf_path),
            "{$batch->reference}-picking-sheet.pdf",
            ['Content-Type' => 'application/pdf'],
        );
    }
}
