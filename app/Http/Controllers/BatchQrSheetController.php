<?php

namespace App\Http\Controllers;

use App\Jobs\GenerateBatchQrSheetJob;
use App\Models\Batch;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class BatchQrSheetController extends Controller
{
    public function __invoke(Request $request, Batch $batch)
    {
        $user = $request->user();
        if (! $user || (! $user->hasRole('admin') &&
            ! $user->stores()->where('id', $batch->store_id)->exists())) {
            abort(403);
        }

        // If the PDF already exists, stream it
        if ($batch->qr_sheet_pdf_path && Storage::disk('local')->exists($batch->qr_sheet_pdf_path)) {
            return response()->streamDownload(
                fn() => print Storage::disk('local')->get($batch->qr_sheet_pdf_path),
                "{$batch->reference}_qr_sheet.pdf",
                ['Content-Type' => 'application/pdf'],
            );
        }

        // Otherwise dispatch the job and show a "preparing" page if 10 mins have passed since the batch was created
        if (! $batch->qr_sheet_pdf_path && $batch->created_at->diffInMinutes(now()) >= 10) {
            GenerateBatchQrSheetJob::dispatch($batch->id);
        }

        return response()->view('pdf.batch-qr-sheet-preparing', [
            'batch' => $batch,
        ], 202);
    }
}
