<?php

namespace App\Http\Controllers\Debug;

use App\Http\Controllers\Controller;
use App\Models\Batch;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class QrSheetPreviewController extends Controller
{
    public function __invoke(Request $request, Batch $batch)
    {
        $user = $request->user();

        if (! $user || ! $user->hasRole('admin')) {
            abort(403);
        }

        $batch->load(['store', 'packs.card.card']);

        $qrSize = (int) $request->integer('qr_size', 56);
        $nameLimit = (int) $request->integer('name_limit', 14);
        $perPage = (int) $request->integer('per_page', 65);

        $rows = $batch->packs()
            ->with('card.card')
            ->orderBy('sequence_no')
            ->get()
            ->map(function ($pack) use ($qrSize, $nameLimit) {
                $inv = $pack->card;
                $card = $inv?->card;
                $token = $inv?->qr_token;

                $qrPng = null;

                if ($token) {
                    $url = route('qr.scan', ['token' => $token]);

                    $png = QrCode::format('png')
                        ->size($qrSize)
                        ->margin(0)
                        ->generate($url);

                    $qrPng = 'data:image/png;base64,' . base64_encode($png);

                }

                return [
                    'sequence' => $pack->sequence_no,
                    'name'     => $card?->name ?? 'Unknown',
                    'name_short' => \Illuminate\Support\Str::limit($card?->name ?? 'Unknown', $nameLimit, ''),
                    'set'      => $card?->set_name ?? '',
                    'number'   => $card?->card_number ?? '',
                    'band'     => $inv?->rarity_band ?? '',
                    'qr_png'   => $qrPng,
                ];
            });

        $pdf = Pdf::loadView('pdf.batch-qr-sheet', [
            'batch'      => $batch,
            'rows'       => $rows,
            'perPage'    => $perPage,
            'qrSize'     => $qrSize,
            'nameLimit'  => $nameLimit,
        ])->setPaper('a4', 'portrait');

        return $pdf->stream("{$batch->reference}_qr_sheet_preview.pdf");
    }
}
