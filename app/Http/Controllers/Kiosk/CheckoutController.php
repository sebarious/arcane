<?php

namespace App\Http\Controllers\Kiosk;

use App\Exceptions\Kiosk\BasketItemsUnavailableException;
use App\Http\Controllers\Controller;
use App\Models\KioskOrder;
use App\Services\Kiosk\KioskCheckoutService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CheckoutController extends Controller
{
    /** POST /kiosk/checkout — snapshots the session's basket into an order, creates a PaymentIntent, and triggers the reader. */
    public function store(Request $request, KioskCheckoutService $checkout): JsonResponse
    {
        $ids = array_map('intval', (array) $request->session()->get('kiosk_basket', []));

        if (empty($ids)) {
            return response()->json(['message' => 'Your basket is empty.'], 422);
        }

        try {
            $order = $checkout->startCheckout($ids, $request->session()->getId());
        } catch (BasketItemsUnavailableException $e) {
            $request->session()->put(
                'kiosk_basket',
                array_values(array_diff($ids, $e->cardInventoryIds)),
            );

            return response()->json([
                'message' => 'Some items in your basket are no longer available.',
                'unavailable_card_ids' => $e->cardInventoryIds,
            ], 409);
        }

        $request->session()->put('kiosk_current_order_id', $order->id);

        return response()->json([
            'data' => [
                'order_id' => $order->id,
                'reference' => $order->reference,
                'total_pence' => $order->total_pence,
            ],
        ]);
    }

    /** POST /kiosk/orders/{order}/cancel — customer backed out at the reader. */
    public function cancel(Request $request, KioskOrder $order, KioskCheckoutService $checkout): JsonResponse
    {
        // Unauthenticated endpoint on a walk-up tablet, so the order has to be
        // tied back to the session that started it — otherwise one kiosk (or
        // anyone guessing an id) could cancel another's in-flight payment.
        if ((int) $request->session()->get('kiosk_current_order_id') !== $order->id) {
            return response()->json(['message' => 'That order is not active on this kiosk.'], 403);
        }

        $order = $checkout->cancel($order);

        $request->session()->forget('kiosk_current_order_id');

        // Cancel arrived too late and the payment stands — clear the basket
        // exactly as the status poll does for a paid order, so the next
        // customer doesn't inherit these items.
        if ($order->status === 'paid') {
            $request->session()->put('kiosk_basket', []);
        }

        return response()->json([
            'data' => [
                'reference' => $order->reference,
                'status' => $order->status,
                'total_pence' => $order->total_pence,
            ],
        ]);
    }
}
