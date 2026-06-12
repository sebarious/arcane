<?php

namespace App\Http\Controllers;

use App\Models\CardInventory;
use Illuminate\Http\Request;

class QrScanController extends Controller
{
    public function __invoke(Request $request, string $token)
    {
        $card = CardInventory::with(['card', 'pack.batch.store'])
            ->where('qr_token', $token)
            ->first();

        if (! $card) {
            return response()->view('qr.invalid', [], 404);
        }

        $pack  = $card->pack;
        $batch = $pack?->batch;
        $store = $batch?->store;

        // If already sold, show a simple message (useful for disputes)
        if ($card->status === 'sold') {
            return response()->view('qr.already-sold', [
                'card'  => $card,
                'store' => $store,
                'batch' => $batch,
                'pack'  => $pack,
            ]);
        }

        // For now, no login required — just a confirmation screen.
        // Later we can require the seller to be logged in and
        // belong to $store.
        return response()->view('qr.confirm', [
            'card'  => $card,
            'store' => $store,
            'batch' => $batch,
            'pack'  => $pack,
        ]);
    }
}
