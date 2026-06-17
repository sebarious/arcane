<?php

namespace App\Http\Controllers;

use App\Models\CardInventory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Events\PackSold;

class QrConfirmController extends Controller
{
    public function __invoke(Request $request, string $token)
    {
        $card = CardInventory::with(['pack.batch.store'])
            ->where('qr_token', $token)
            ->first();

        if (! $card) {
            return redirect()->route('qr.scan', ['token' => $token]);
        }

        if ($card->status === 'sold') {
            return redirect()->route('qr.scan', ['token' => $token]);
        }

        DB::transaction(function () use ($card, $request) {
            $card->update([
                'status'              => 'sold',
                'delisted_at'         => now(),
                'delisted_by_user_id' => $request->user()?->id,
            ]);

            if ($card->pack) {
                $card->pack->update([
                    'status' => 'sold',
                    'sold_at' => now(),
                ]);
            }

            if ($card->pack) {
                $card->pack->update([
                    'status' => 'sold',
                    'sold_at' => now(),
                ]);
                // event(new PackSold($card->pack));
            }
        });

        return response()->view('qr.already-sold', [
            'card'  => $card,
            'store' => $card->pack?->batch?->store,
            'batch' => $card->pack?->batch,
            'pack'  => $card->pack,
        ]);
    }
}
