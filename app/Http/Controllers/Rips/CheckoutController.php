<?php

namespace App\Http\Controllers\Rips;

use App\Http\Controllers\Controller;
use App\Models\RipOrder;
use App\Services\Rips\RipCheckoutService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CheckoutController extends Controller
{
    /**
     * POST /rips/checkout — starts an order for one or more packs and opens a
     * Stripe PaymentIntent for the total. Auth-gated: this is the point where
     * AuthModal.vue must have already logged the buyer in.
     *
     * Expects: items: [{ rip_pack_id, quantity }]
     */
    public function store(Request $request, RipCheckoutService $checkout): JsonResponse
    {
        $data = $request->validate([
            'items' => ['required', 'array', 'min:1'],
            'items.*.rip_pack_id' => ['required', 'integer', 'exists:rip_packs,id'],
            'items.*.quantity' => ['required', 'integer', 'min:1', 'max:10'],
        ]);

        try {
            $order = $checkout->startCheckout($request->user(), $data['items']);
        } catch (\RuntimeException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }

        return response()->json([
            'data' => [
                'order_reference' => $order->reference,
                'client_secret' => $order->stripe_client_secret,
                'total_pence' => $order->total_pence,
            ],
        ]);
    }

    /**
     * POST /rips/checkout/wallet — pays for one or more packs instantly out
     * of the customer's wallet balance. No polling needed — paid, drawn, and
     * ready to open by the time this responds.
     *
     * Expects: items: [{ rip_pack_id, quantity }]
     */
    public function payFromWallet(Request $request, RipCheckoutService $checkout): JsonResponse
    {
        $data = $request->validate([
            'items' => ['required', 'array', 'min:1'],
            'items.*.rip_pack_id' => ['required', 'integer', 'exists:rip_packs,id'],
            'items.*.quantity' => ['required', 'integer', 'min:1', 'max:10'],
        ]);

        try {
            $order = $checkout->payFromWallet($request->user(), $data['items']);
        } catch (\RuntimeException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }

        return response()->json([
            'data' => [
                'order_reference' => $order->reference,
                'status' => $order->status,
                'rip_ids' => $order->rips()->pluck('id'),
            ],
        ]);
    }

    /**
     * GET /rips/checkout/{ripOrder}/status — polled by the frontend right
     * after Stripe confirms the PaymentIntent client-side. finalize() is
     * idempotent, same "poll or webhook, whichever gets there first" shape as
     * Kiosk\OrderStatusController.
     */
    public function status(Request $request, RipOrder $ripOrder, RipCheckoutService $checkout): JsonResponse
    {
        abort_unless($ripOrder->user_id === $request->user()->id, 403);

        if ($ripOrder->status === 'pending_payment' && $ripOrder->stripe_payment_intent_id) {
            $ripOrder = $checkout->finalize($ripOrder);
        }

        return response()->json([
            'data' => [
                'reference' => $ripOrder->reference,
                'status' => $ripOrder->status,
                'rip_ids' => $ripOrder->status === 'paid' ? $ripOrder->rips()->pluck('id') : [],
            ],
        ]);
    }
}
