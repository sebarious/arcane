<?php

namespace App\Http\Controllers\Rips;

use App\Http\Controllers\Controller;
use App\Models\Rip;
use App\Services\Rips\RipCheckoutService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;

class OpenController extends Controller
{
    /**
     * GET /rips/my/{rip} — the reveal page. Deliberately never sends card
     * details here, even though the draw already happened at payment time —
     * only the POST below (the moment the customer actually taps to tear
     * the pack open) is allowed to reveal what's inside.
     */
    public function show(Request $request, Rip $rip)
    {
        $this->authorizeOwner($request, $rip);

        return Inertia::render('Rips/Open', [
            'rip' => [
                'id' => $rip->id,
                'pack_name' => $rip->pack_name,
                'price_pence' => $rip->price_pence,
                'buy_back_percentage' => $rip->buy_back_percentage,
                'ready' => $rip->order->status === 'paid' && $rip->isDrawn(),
                'opened' => $rip->isOpened(),
                'decision' => $rip->decision,
                'sold_back_pence' => $rip->sold_back_pence,
                'card' => $rip->isOpened() ? [
                    'name' => $rip->card?->card_name,
                    'set' => $rip->card?->set_name,
                    'number' => $rip->card?->card_number,
                    'image' => $rip->card?->image_url,
                    'band' => $rip->card?->rarity_band,
                    'market_value_pence' => $rip->card?->market_value_pence,
                ] : null,
            ],
        ]);
    }

    /** POST /rips/my/{rip}/open — the actual tear. First (and only) point card details are ever sent to the client. */
    public function open(Request $request, Rip $rip, RipCheckoutService $checkout): JsonResponse
    {
        $this->authorizeOwner($request, $rip);

        try {
            $rip = $checkout->open($rip);
        } catch (\RuntimeException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }

        return response()->json([
            'data' => [
                'card' => [
                    'name' => $rip->card?->card_name,
                    'set' => $rip->card?->set_name,
                    'number' => $rip->card?->card_number,
                    'image' => $rip->card?->image_url,
                    'band' => $rip->card?->rarity_band,
                    'market_value_pence' => $rip->card?->market_value_pence,
                ],
                'buy_back_pence' => (int) round(($rip->card?->market_value_pence ?? 0) * $rip->buy_back_percentage),
            ],
        ]);
    }

    private function authorizeOwner(Request $request, Rip $rip): void
    {
        abort_unless($rip->order->user_id === $request->user()->id, 403);
    }
}
