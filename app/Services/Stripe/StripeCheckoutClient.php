<?php

namespace App\Services\Stripe;

use Stripe\PaymentIntent;
use Stripe\StripeClient;

/**
 * Digital Rips' online checkout — a standard browser PaymentIntent confirmed
 * client-side via Stripe's Payment Element (publishable key, see
 * config('services.stripe.key')), unlike StripeTerminalClient's server-driven
 * card-present flow for the in-store Kiosk. Same lazy-client pattern as that
 * class, for the same reason: this gets injected into pack-browsing requests
 * that never touch Stripe at all.
 */
class StripeCheckoutClient
{
    private ?StripeClient $client = null;

    private function client(): StripeClient
    {
        return $this->client ??= new StripeClient(config('services.stripe.secret'));
    }

    public function createPaymentIntent(int $amountPence, string $currency = 'gbp'): PaymentIntent
    {
        return $this->client()->paymentIntents->create([
            'amount' => $amountPence,
            'currency' => $currency,
            'automatic_payment_methods' => ['enabled' => true],
        ]);
    }

    public function retrievePaymentIntent(string $paymentIntentId): PaymentIntent
    {
        return $this->client()->paymentIntents->retrieve($paymentIntentId);
    }
}
