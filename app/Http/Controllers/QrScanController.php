<?php

namespace App\Http\Controllers;

use App\Services\Packs\PackRedeemer;
use Illuminate\Http\Request;

class QrScanController extends Controller
{
    public function __invoke(Request $request, string $token, PackRedeemer $redeemer)
    {
        $card = $redeemer->findByToken($token);

        if (! $card) {
            return response()->view('qr.invalid', [], 404);
        }

        $pack = $card->pack;
        $batch = $pack?->batch;
        $store = $batch?->store;

        if (! $store || ! $batch) {
            return response()->view('qr.invalid', [], 404);
        }

        $redeemer->redeem($card, $request->user()?->id);

        return redirect()->route('stores.lists.show', [
            'store' => $store->slug,
            'batch' => $batch->id,
        ]);
    }
}
