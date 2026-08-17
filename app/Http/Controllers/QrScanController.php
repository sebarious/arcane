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

        // Scanning before the batch has actually shipped should never
        // consume a pack — see PackRedeemer::isEligibleForRedemption().
        if (! $redeemer->isEligibleForRedemption($card)) {
            return response()->view('qr.not-dispatched');
        }

        // true whenever this scan confirmed or repaired anything (including
        // a desynced card/pack pair — see PackRedeemer::redeem) — false only
        // when the scan was a genuine, fully-consistent rescan.
        $confirmed = $redeemer->redeem($card, $request->user()?->id);

        return redirect()
            ->route('stores.lists.show', [
                'store' => $store->slug,
                'batch' => $batch->id,
            ])
            ->with(
                $confirmed ? 'success' : 'status',
                $confirmed
                    ? "Pull confirmed! You got {$card->card_name} — enjoy the chase."
                    : 'This card was already confirmed sold — nothing new to do here.',
            );
    }
}
