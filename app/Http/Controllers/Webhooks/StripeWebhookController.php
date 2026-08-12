<?php

namespace App\Http\Controllers\Webhooks;

use App\Http\Controllers\Controller;
use App\Models\KioskOrder;
use App\Services\Kiosk\KioskCheckoutService;
use Illuminate\Http\Request;
use Stripe\Exception\SignatureVerificationException;
use Stripe\Webhook;
use Symfony\Component\HttpFoundation\Response;

/**
 * Authoritative confirmation path for kiosk payments — the kiosk's own poll
 * (OrderStatusController) also tries to finalize, but this is what fires even
 * if the tablet loses connectivity right after a successful tap. finalize()
 * is idempotent, so it doesn't matter which one gets there first.
 */
class StripeWebhookController extends Controller
{
    public function __invoke(Request $request, KioskCheckoutService $checkout): Response
    {
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
            $order = KioskOrder::where('stripe_payment_intent_id', $event->data->object->id)->first();

            if ($order) {
                $checkout->finalize($order);
            }
        }

        return response('', 200);
    }
}
