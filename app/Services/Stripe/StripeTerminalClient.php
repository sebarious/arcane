<?php

namespace App\Services\Stripe;

use Stripe\PaymentIntent;
use Stripe\StripeClient;
use Stripe\Terminal\Reader;

/**
 * Thin wrapper around the three Stripe API calls the kiosk's server-driven
 * Terminal integration needs. No client-side Stripe.js/publishable key is
 * involved anywhere — the backend creates the PaymentIntent and tells the
 * (pre-registered) reader to collect it directly via the API; Stripe pushes
 * the result back as a webhook.
 */
class StripeTerminalClient
{
    private ?StripeClient $client = null;

    /**
     * Lazy — this class gets injected (via KioskCheckoutService) into every
     * kiosk request, including search/basket ones that never touch Stripe at
     * all, so constructing the SDK client up front would mean a missing
     * STRIPE_SECRET breaks browsing, not just checkout.
     */
    private function client(): StripeClient
    {
        return $this->client ??= new StripeClient(config('services.stripe.secret'));
    }

    public function createPaymentIntent(int $amountPence, string $currency = 'gbp'): PaymentIntent
    {
        return $this->client()->paymentIntents->create([
            'amount' => $amountPence,
            'currency' => $currency,
            'payment_method_types' => ['card_present'],
            'capture_method' => 'automatic',
        ]);
    }

    /** Tells the configured reader to prompt the customer and collect this PaymentIntent. */
    public function processPaymentIntentOnReader(string $paymentIntentId): Reader
    {
        return $this->client()->terminal->readers->processPaymentIntent(
            config('services.stripe.terminal_reader_id'),
            ['payment_intent' => $paymentIntentId],
        );
    }

    public function retrievePaymentIntent(string $paymentIntentId): PaymentIntent
    {
        return $this->client()->paymentIntents->retrieve($paymentIntentId);
    }

    /** Cancels an in-progress reader action — used when a checkout is abandoned before payment completes. */
    public function cancelReaderAction(): Reader
    {
        return $this->client()->terminal->readers->cancelAction(
            config('services.stripe.terminal_reader_id'),
        );
    }

    /**
     * Test mode only — simulates a card being tapped/inserted on the
     * configured reader, completing whatever PaymentIntent it's currently
     * processing. Stripe's test_helpers endpoints reject calls made with a
     * live secret key, so this can't do anything against a real reader.
     * See App\Console\Commands\SimulateKioskPaymentCommand.
     */
    public function simulateCardPresentment(): Reader
    {
        return $this->client()->testHelpers->terminal->readers->presentPaymentMethod(
            config('services.stripe.terminal_reader_id'),
        );
    }
}
