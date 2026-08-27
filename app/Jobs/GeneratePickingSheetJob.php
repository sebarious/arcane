<?php

namespace App\Jobs;

use App\Models\Batch;
use App\Services\Batches\PickingSheetGenerator;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;

class GeneratePickingSheetJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $timeout = 240;

    public int $tries = 1;

    public function __construct(public int $batchId) {}

    public function handle(PickingSheetGenerator $generator): void
    {
        $batch = Batch::findOrFail($this->batchId);

        // Captured BEFORE generate() runs — generate() stamps picked_at on
        // whatever it returns as a side effect, so calling this after would
        // also pick up the cards generate() just processed in this same run.
        $alreadyPickedLots = $generator->alreadyPicked($batch);

        $lots = $generator->generate($batch);

        if ($lots->isEmpty()) {
            return;
        }

        $pdf = Pdf::loadView('pdf.picking-sheet', [
            'batch' => $batch,
            'lots' => $lots,
            'alreadyPickedLots' => $alreadyPickedLots,
        ])->setPaper('a4', 'portrait');

        $path = "picking-sheets/{$batch->reference}-".now()->format('YmdHis').'.pdf';
        Storage::disk('local')->put($path, $pdf->output());

        $batch->update(['picking_sheet_pdf_path' => $path]);
    }
}
