<?php

namespace App\Http\Controllers;

use App\Models\CardInventory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class QrScanController extends Controller
{
    public function __invoke(Request $request, string $token)
    {
        $card = CardInventory::with('pack.batch.store')
            ->where('qr_token', $token)
            ->first();

        if (! $card) {
            return response()->view('qr.invalid', [], 404);
        }

        $pack = $card->pack;
        $batch = $pack?->batch;
        $store = $batch?->store;

        if (! $store || ! $batch) {
            return response()->view('qr.invalid', [], 404);
        }

        if ($card->status !== 'sold') {
            DB::transaction(function () use ($card, $pack, $request) {
                $card->update([
                    'status' => 'sold',
                    'delisted_at' => now(),
                    'delisted_by_user_id' => $request->user()?->id,
                ]);

                $pack?->update([
                    'status' => 'sold',
                    'sold_at' => now(),
                ]);
            });
        }

        return redirect()->route('stores.lists.show', [
            'store' => $store->slug,
            'batch' => $batch->id,
        ]);
    }
}
