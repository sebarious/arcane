<?php

namespace App\Http\Controllers;

use App\Models\Batch;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class BatchQrSheetController extends Controller
{
    public function __invoke(Request $request, Batch $batch)
    {
        // Authorize: only admins (and later, the store owner) can access
        if (! $request->user()?->hasRole('admin')) {
            abort(403);
        }

        $batch->load(['packs.card.card']); // packs -> cardInventory -> card

        // Prepare data for the view
        $rows = $batch->packs()
            ->with('card.card')
            ->orderBy('sequence_no')
            ->get()
            ->map(function ($pack) use ($batch, $request) {
                $cardInventory = $pack->card;
                $card          = $cardInventory?->card;
                $token = $cardInventory?->qr_token;
                $qrUrl = $token
                    ? route('qr.scan', ['token' => $token])
                    : null;
                $qrPng = null;
                if ($qrUrl) {
                    $png = QrCode::format('png')
                        ->size(140)
                        ->margin(0)
                        ->generate($qrUrl);
                    $qrPng = 'data:image/png;base64,' . base64_encode($png);
                }
                return [
                    'sequence' => $pack->sequence_no,
                    'name'     => $card?->name ?? 'Unknown',
                    'set'      => $card?->set_name ?? '',
                    'number'   => $card?->card_number ?? '',
                    'band'     => $cardInventory?->rarity_band ?? '',
                    'qr_png'   => $qrPng,
                ];
            });

        $pdf = Pdf::loadView('pdf.batch-qr-sheet', [
            'batch' => $batch,
            'rows'  => $rows,
        ])->setPaper('a4', 'portrait');

        $filename = "{$batch->reference}_qr_sheet.pdf";

        return $pdf->download($filename);
    }
}
