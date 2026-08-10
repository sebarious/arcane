<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Batch;
use App\Models\Store;
use App\Services\Packs\PackRedeemer;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PackSaleController extends Controller
{
    /** POST /api/v1/batches/{reference}/packs/{pack}/sold — mark a pack's card sold. Idempotent on rescan. */
    public function store(Request $request, string $reference, int $pack, PackRedeemer $redeemer): JsonResponse
    {
        /** @var Store $store */
        $store = $request->attributes->get('api_store');

        $batch = Batch::visibleToStoreApi($store)->where('reference', $reference)->first();

        if (! $batch) {
            return response()->json(['message' => 'Batch not found.'], 404);
        }

        $packModel = $batch->packs()->with('card')->find($pack);

        if (! $packModel || ! $packModel->card) {
            return response()->json(['message' => 'Pack not found in this batch.'], 404);
        }

        $wasAlreadySold = $packModel->card->status === 'sold';

        $redeemer->redeem($packModel->card, $store->user_id);

        $packModel->refresh();

        return response()->json([
            'status' => $wasAlreadySold ? 'already_sold' : 'sold',
            'data' => [
                'id' => $packModel->id,
                'is_sold' => true,
                'sold_at' => $packModel->sold_at?->toIso8601String(),
            ],
        ]);
    }
}
