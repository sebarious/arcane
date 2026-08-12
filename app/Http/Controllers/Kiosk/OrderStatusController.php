<?php

namespace App\Http\Controllers\Kiosk;

use App\Http\Controllers\Controller;
use App\Models\KioskOrder;
use App\Services\Kiosk\KioskCheckoutService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OrderStatusController extends Controller
{
    /**
     * GET /kiosk/orders/{order}/status — polled by the kiosk while waiting on
     * the reader. Also opportunistically tries to finalize the order itself
     * (KioskCheckoutService::finalize() is idempotent), so a slow/missed
     * webhook doesn't leave the customer stuck on a spinner.
     */
    public function __invoke(Request $request, KioskOrder $order, KioskCheckoutService $checkout): JsonResponse
    {
        if ($order->status === 'pending_payment' && $order->stripe_payment_intent_id) {
            $order = $checkout->finalize($order);
        }

        if ($order->status === 'paid') {
            $request->session()->forget('kiosk_current_order_id');
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
