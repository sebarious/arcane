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
    public int $tries   = 1;

    public function __construct(public int $batchId) {}

    public function handle(): void
    {
        $batch = Batch::with(['store', 'packs.card.card'])->findOrFail($this->batchId);

        $rows = $batch->packs()
            ->with('card.card')
            ->orderBy('sequence_no')
            ->get()
            ->map(function ($pack) {
                $inv   = $pack->card;
                $card  = $inv?->card;
                $token = $inv?->qr_token;

                $qrPng = null;
                if ($token) {
                    $url = route('qr.scan', ['token' => $token]);
                    $png = QrCode::format('png')->size(140)->margin(0)->generate($url);
                    $qrPng = 'data:image/png;base64,' . base64_encode($png);
                }

                return [
                    'sequence' => $pack->sequence_no,
                    'name'     => $card?->name ?? 'Unknown',
                    'set'      => $card?->set_name ?? '',
                    'number'   => $card?->card_number ?? '',
                    'band'     => $inv?->rarity_band ?? '',
                    'qr_png'   => $qrPng,
                ];
            });

        $pdf = Pdf::loadView('pdf.batch-qr-sheet', [
            'batch' => $batch,
            'rows'  => $rows,
        ])->setPaper('a4', 'portrait');

        $path = "qr-sheets/{$batch->reference}.pdf";
        Storage::disk('local')->put($path, $pdf->output());

        $batch->update(['qr_sheet_pdf_path' => $path]);
    }
}
