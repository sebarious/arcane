<?php

namespace App\Http\Controllers\Kiosk;

use App\Http\Controllers\Controller;
use App\Models\CardInventory;
use App\Services\Kiosk\KioskBasketService;
use App\Services\Kiosk\KioskCheckoutService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

/**
 * Basket membership is Laravel session state (a plain array of card_inventory
 * ids under 'kiosk_basket'); the actual hold on each card is a real DB row
 * lock via KioskBasketService, keyed to this same session id.
 */
class BasketController extends Controller
{
    public function index(Request $request, KioskBasketService $basket, KioskCheckoutService $checkout): JsonResponse
    {
        $ids = $this->sessionIds($request);
        $held = $basket->stillHeld($ids, $request->session()->getId());

        if (count($held) !== count($ids)) {
            $request->session()->put('kiosk_basket', $held);
        }

        return $this->basketResponse(CardInventory::whereIn('id', $held)->get(), $checkout);
    }

    public function store(Request $request, KioskBasketService $basket, KioskCheckoutService $checkout): JsonResponse
    {
        $validated = $request->validate(['card_inventory_id' => ['required', 'integer']]);

        $card = $basket->reserve((int) $validated['card_inventory_id'], $request->session()->getId());

        if (! $card) {
            return response()->json(['message' => 'That card is no longer available.'], 409);
        }

        $ids = array_values(array_unique([...$this->sessionIds($request), $card->id]));
        $request->session()->put('kiosk_basket', $ids);

        return $this->basketResponse(CardInventory::whereIn('id', $ids)->get(), $checkout);
    }

    public function destroy(Request $request, int $cardInventoryId, KioskBasketService $basket, KioskCheckoutService $checkout): JsonResponse
    {
        $basket->release($cardInventoryId, $request->session()->getId());

        $ids = array_values(array_diff($this->sessionIds($request), [$cardInventoryId]));
        $request->session()->put('kiosk_basket', $ids);

        return $this->basketResponse(CardInventory::whereIn('id', $ids)->get(), $checkout);
    }

    /** DELETE /kiosk/basket — the "Clear basket" button: releases every hold this session has in one go. */
    public function clear(Request $request, KioskBasketService $basket): JsonResponse
    {
        $basket->releaseAllForSession($request->session()->getId());
        $request->session()->put('kiosk_basket', []);

        return response()->json(['data' => [], 'total_pence' => 0]);
    }

    /** @return int[] */
    private function sessionIds(Request $request): array
    {
        return array_map('intval', (array) $request->session()->get('kiosk_basket', []));
    }

    private function basketResponse(Collection $cards, KioskCheckoutService $checkout): JsonResponse
    {
        $items = $cards->map(fn (CardInventory $card) => $checkout->present($card))->values();

        return response()->json([
            'data' => $items,
            'total_pence' => $items->sum('price_pence'),
        ]);
    }
}
