<?php

namespace App\Http\Controllers\Catalogue;

use App\Http\Controllers\Controller;
use App\Services\Kiosk\KioskOpenOrderService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * The one thing the browse-only catalogue tablet is allowed to write.
 *
 * No PIN and no auth, unlike the till: this is a walk-up tablet and the
 * worst a stranger can do with it is create a shopping list nobody collects
 * (which the nightly arcane:purge-open-kiosk-orders sweeps up). It takes no
 * money and holds no stock — see KioskOpenOrderService.
 */
class OpenOrderController extends Controller
{
    /** POST /catalogue/kiosk/order */
    public function store(Request $request, KioskOpenOrderService $openOrders): JsonResponse
    {
        $validated = $request->validate([
            'card_inventory_ids' => ['required', 'array', 'min:1', 'max:100'],
            'card_inventory_ids.*' => ['integer'],
        ]);

        try {
            $order = $openOrders->create($validated['card_inventory_ids']);
        } catch (\RuntimeException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }

        return response()->json(['data' => [
            'reference' => $order->reference,
            'short_reference' => $order->shortReference(),
            'total_pence' => $order->total_pence,
            'item_count' => $order->items()->count(),
        ]]);
    }
}
