<?php

namespace App\Http\Controllers\Seller;

use App\Http\Controllers\Controller;
use App\Services\Packs\PackRedeemer;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ScanStationController extends Controller
{
    public function show(): Response
    {
        return Inertia::render('Seller/ScanStation');
    }

    /**
     * Accepts whatever a barcode/QR scanner types — the full pack URL (its
     * usual "keyboard wedge" output) or a bare token — marks the pack sold,
     * and returns JSON so the page can show the result inline without
     * navigating away, ready for the next scan.
     */
    public function scan(Request $request, PackRedeemer $redeemer): JsonResponse
    {
        $data = $request->validate([
            'code' => ['required', 'string', 'max:2048'],
        ]);

        $token = $this->extractToken($data['code']);
        $card = $token ? $redeemer->findByToken($token) : null;

        if (! $card) {
            return response()->json(['status' => 'not_found'], 404);
        }

        $pack = $card->pack;
        $batch = $pack?->batch;
        $store = $batch?->store;

        if (! $store || ! $batch) {
            return response()->json(['status' => 'not_found'], 404);
        }

        $user = $request->user();

        if (! $user->stores()->where('id', $store->id)->exists()) {
            return response()->json(['status' => 'wrong_store'], 403);
        }

        $wasAlreadySold = $card->status === 'sold';
        $redeemer->redeem($card, $user->id);

        return response()->json([
            'status' => $wasAlreadySold ? 'already_sold' : 'sold',
            'card' => [
                'name' => $card->card_name,
                'set' => $card->set_name,
                'image' => $card->image_url,
                'rarity_band' => $card->rarity_band,
            ],
            'batch' => ['reference' => $batch->reference],
            'store' => ['name' => $store->name],
        ]);
    }

    private function extractToken(string $raw): ?string
    {
        $raw = trim($raw);

        if ($raw === '') {
            return null;
        }

        if (! str_contains($raw, '/')) {
            return $raw;
        }

        // Full scanned URL (e.g. https://.../q/{token}) — take the last path segment.
        $path = parse_url($raw, PHP_URL_PATH) ?? $raw;
        $segments = array_values(array_filter(explode('/', $path)));

        return end($segments) ?: null;
    }
}
