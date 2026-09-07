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

        // Nothing new to pick AND nothing already picked either — a genuinely
        // empty batch, nothing to show at all. But if everything's already
        // been picked (lots empty, alreadyPickedLots not), still regenerate:
        // otherwise the file left behind is whatever the *previous* run
        // produced — which, if that run predates a later correction (e.g. a
        // CardSwapper swap this run's already-picked section now accounts
        // for), would leave a stale, incomplete PDF in place forever with no
        // way to self-correct.
        if ($lots->isEmpty() && $alreadyPickedLots->isEmpty()) {
            return;
        }

        $pdf = Pdf::loadView('pdf.picking-sheet', [
            'batch' => $batch,
            'lots' => $lots,
            'alreadyPickedLots' => $alreadyPickedLots,
            'specialHandling' => $generator->specialHandling($batch),
        ])->setPaper('a4', 'portrait');

        $path = "picking-sheets/{$batch->reference}-".now()->format('YmdHis').'.pdf';
        Storage::disk('local')->put($path, $pdf->output());

        $batch->update(['picking_sheet_pdf_path' => $path]);
    }
}
