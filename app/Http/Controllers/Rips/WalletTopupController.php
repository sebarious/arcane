<?php

namespace App\Http\Controllers\Rips;

use App\Http\Controllers\Controller;
use App\Models\RipWalletTopup;
use App\Services\Rips\RipWalletTopupService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class WalletTopupController extends Controller
{
    /** POST /rips/wallet/topup — starts a card payment to add funds to the wallet. */
    public function store(Request $request, RipWalletTopupService $topups): JsonResponse
    {
        $data = $request->validate([
            'amount_pence' => ['required', 'integer', 'min:1'],
        ]);

        try {
            $topup = $topups->startTopup($request->user(), $data['amount_pence']);
        } catch (\RuntimeException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }

        return response()->json([
            'data' => [
                'id' => $topup->id,
                'client_secret' => $topup->stripe_client_secret,
            ],
        ]);
    }

    /**
     * GET /rips/wallet/topup/{ripWalletTopup}/status — polled right after
     * Stripe confirms the PaymentIntent client-side. finalize() is
     * idempotent, same shape as Rips\CheckoutController::status().
     */
    public function status(Request $request, RipWalletTopup $ripWalletTopup, RipWalletTopupService $topups): JsonResponse
    {
        abort_unless($ripWalletTopup->user_id === $request->user()->id, 403);

        if ($ripWalletTopup->status === 'pending_payment' && $ripWalletTopup->stripe_payment_intent_id) {
            $ripWalletTopup = $topups->finalize($ripWalletTopup);
        }

        return response()->json([
            'data' => [
                'status' => $ripWalletTopup->status,
                'balance_pence' => $ripWalletTopup->status === 'paid'
                    ? $ripWalletTopup->user->ripWallet?->credit_balance_pence
                    : null,
            ],
        ]);
    }
}
