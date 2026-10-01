<?php

namespace App\Http\Controllers\Kiosk;

use App\Http\Controllers\Controller;
use App\Models\CardInventory;
use App\Models\KioskOrder;
use App\Services\Kiosk\KioskBasketService;
use App\Services\Kiosk\KioskCheckoutService;
use App\Services\Kiosk\KioskOpenOrderService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * The till's side of open orders: the queue of shopping lists customers
 * built on the catalogue tablet, waiting to be paid for at the counter.
 * Behind the day's PIN like the rest of the till.
 */
class OpenOrderController extends Controller
{
    public function index(KioskOpenOrderService $openOrders): JsonResponse
    {
        return response()->json([
            'data' => $openOrders->open()->map(fn (KioskOrder $o) => $openOrders->present($o))->values(),
        ]);
    }

    /** Staff discarding a request nobody came to collect. */
    public function destroy(KioskOrder $order, KioskOpenOrderService $openOrders): JsonResponse
    {
        abort_unless($order->status === KioskOpenOrderService::STATUS_OPEN, 404);

        $openOrders->discard($order);

        return response()->json(['data' => ['deleted' => true]]);
    }

    /**
     * Pulls an open order into the live basket so it can be paid for.
     *
     * Replaces whatever was in the basket rather than merging: the till is
     * serving this customer now, and silently mixing in a previous
     * customer's leftovers would be a charging error waiting to happen.
     */
    public function checkout(
        Request $request,
        KioskOrder $order,
        KioskOpenOrderService $openOrders,
        KioskCheckoutService $checkout,
        KioskBasketService $basket,
    ): JsonResponse {
        abort_unless($order->status === KioskOpenOrderService::STATUS_OPEN, 404);

        $sessionToken = $request->session()->getId();

        $basket->releaseAllForSession($sessionToken);
        $request->session()->forget(['kiosk_custom_lines', 'kiosk_discount']);

        $result = $openOrders->collect($order->load('items'), $sessionToken);

        $request->session()->put('kiosk_basket', $result['reserved']);

        $summary = $checkout->summarise(CardInventory::whereIn('id', $result['reserved'])->get());

        return response()->json($summary + [
            'reference' => $order->shortReference(),
            // Named, not counted: staff need to tell the customer which cards
            // went rather than just how many.
            'unavailable' => $result['unavailable'],
        ]);
    }
}
