<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Batch;
use App\Models\Pack;
use App\Models\Store;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BatchPacksController extends Controller
{
    /** GET /api/v1/batches/{reference} — every pack in an active batch belonging to the authenticated store. */
    public function show(Request $request, string $reference): JsonResponse
    {
        /** @var Store $store */
        $store = $request->attributes->get('api_store');

        $batch = Batch::where('reference', $reference)->where('store_id', $store->id)->first();

        if (! $batch) {
            return response()->json(['message' => 'Batch not found.'], 404);
        }

        if ($batch->status !== 'committed' || $batch->isMerged()) {
            return response()->json(['message' => 'This batch is not currently active.'], 409);
        }

        $packs = $batch->packs()->with('card')->orderBy('sequence_no')->get();

        return response()->json([
            'data' => $packs->map(fn (Pack $pack) => $this->transform($pack))->values(),
        ]);
    }

    private function transform(Pack $pack): array
    {
        $card = $pack->card;

        return [
            'id' => $pack->id,
            'image_url' => $card?->image_url,
            'title' => $card?->card_name,
            'rarity' => $card?->rarity,
            'is_sold' => $pack->status === 'sold',
            'sold_at' => $pack->sold_at?->toIso8601String(),
            'market_price' => $card?->market_value_pence !== null ? round($card->market_value_pence / 100, 2) : null,
            'market_price_last_synced_at' => $card?->market_value_updated_at?->toIso8601String(),
            'pokepulse_last_synced_at' => $card?->synced_at?->toIso8601String(),
            'qr_url' => $card?->qr_token ? route('qr.scan', ['token' => $card->qr_token]) : null,
        ];
    }
}
