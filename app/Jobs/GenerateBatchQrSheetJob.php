<?php

namespace App\Jobs;

use App\Models\Batch;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class GenerateBatchQrSheetJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $timeout = 300; // 5 minutes

    public int $tries = 1;

    public function __construct(public int $batchId) {}

    public function handle(): void
    {
        $batch = Batch::with(['store', 'packs.card'])->findOrFail($this->batchId);

        // Same order as PickingSheetGenerator (lot alphabetical, then chaosSortKey
        // within a lot) rather than pack-number order — staff work through the
        // picking sheet lot by lot, and if this sheet follows the same order they
        // can grab the next QR sticker as they pull each card instead of hunting
        // for its pack number across the whole sheet.
        $rows = $batch->packs()
            ->with('card')
            ->get()
            ->groupBy(fn ($pack) => $pack->card?->acquisition_lot ?? '(no lot recorded)')
            ->sortKeys()
            ->flatMap(fn ($packsInLot) => $packsInLot->sortBy(fn ($pack) => $pack->card?->chaosSortKey() ?? ''))
            ->values()
            ->map(function ($pack) {
                $inv = $pack->card;
                $token = $inv?->qr_token;

                $qrPng = null;
                if ($token) {
                    $url = route('qr.scan', ['token' => $token]);
                    // Printed size is unchanged (still displayed in a 52x52px box in
                    // the blade view below) — what changed is the *source* PNG: it's
                    // rendered at far higher native resolution (400 vs 56) so the
                    // printed edges are crisp rather than a coarse bitmap stretched
                    // through PDF rendering, and margin(4) bakes a real ISO-standard
                    // quiet zone into the image itself rather than relying on the
                    // surrounding table cell's thin, inconsistent padding (which on
                    // one side is just body text a few px away). Dedicated handheld
                    // CCD/laser scanners depend on a clean quiet zone to even locate
                    // the code far more than a phone camera does — this was reported
                    // as the cause of scan failures with those scanners in the field.
                    $png = QrCode::format('png')->size(400)->margin(4)->generate($url);
                    $qrPng = 'data:image/png;base64,'.base64_encode($png);
                }

                return [
                    'sequence' => $pack->sequence_no,
                    'name' => $inv?->card_name ?? 'Unknown',
                    'set' => $inv?->set_name ?? '',
                    'number' => $inv?->card_number ?? '',
                    'band' => $inv?->rarity_band ?? '',
                    'qr_png' => $qrPng,
                ];
            });

        $pdf = Pdf::loadView('pdf.batch-qr-sheet', [
            'batch' => $batch,
            'rows' => $rows,
        ])->setPaper('a4', 'portrait');

        $path = "qr-sheets/{$batch->reference}.pdf";
        Storage::disk('local')->put($path, $pdf->output());

        $batch->update(['qr_sheet_pdf_path' => $path]);
    }
}
