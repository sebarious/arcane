<?php

namespace App\Http\Controllers\Webhooks;

use App\Http\Controllers\Controller;
use App\Models\KioskOrder;
use App\Models\RipOrder;
use App\Models\RipWalletTopup;
use App\Services\Kiosk\KioskCheckoutService;
use App\Services\Rips\RipCheckoutService;
use App\Services\Rips\RipWalletTopupService;
use Illuminate\Http\Request;
use Stripe\Exception\SignatureVerificationException;
use Stripe\Webhook;
use Symfony\Component\HttpFoundation\Response;

/**
 * Authoritative confirmation path for kiosk (in-person), Digital Rips pack
 * purchases, and wallet top-ups — each channel's own poll also tries to
 * finalize, but this is what fires even if the customer navigates away or
 * loses connectivity right after paying. Every finalize() here is
 * idempotent, so it doesn't matter which path gets there first.
 */
class StripeWebhookController extends Controller
{
    public function __invoke(
        Request $request,
        KioskCheckoutService $kioskCheckout,
        RipCheckoutService $ripCheckout,
        RipWalletTopupService $ripTopups,
    ): Response {
        try {
            $event = Webhook::constructEvent(
                $request->getContent(),
                (string) $request->header('Stripe-Signature'),
                (string) config('services.stripe.webhook_secret'),
            );
        } catch (\UnexpectedValueException|SignatureVerificationException) {
            return response('Invalid signature', 400);
        }

        if ($event->type === 'payment_intent.succeeded') {
            $paymentIntentId = $event->data->object->id;

            $order = KioskOrder::where('stripe_payment_intent_id', $paymentIntentId)->first();

            if ($order) {
                $kioskCheckout->finalize($order);

                return response('', 200);
            }

            $ripOrder = RipOrder::where('stripe_payment_intent_id', $paymentIntentId)->first();

            if ($ripOrder) {
                $ripCheckout->finalize($ripOrder);

                return response('', 200);
            }

            $topup = RipWalletTopup::where('stripe_payment_intent_id', $paymentIntentId)->first();

            if ($topup) {
                $ripTopups->finalize($topup);
            }
        }

        return response('', 200);
    }
}
