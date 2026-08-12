<?php

namespace App\Console\Commands;

use App\Models\KioskOrder;
use App\Services\Stripe\StripeTerminalClient;
use Illuminate\Console\Command;

/**
 * Local/test-mode convenience so testing the kiosk checkout flow doesn't
 * require hand-building a Workbench/curl request every time — see the
 * "How do I update stripe?" / kiosk testing conversation this came out of.
 * Harmless to leave in the codebase: Stripe's test_helpers endpoints simply
 * reject calls made with a live secret key, so this can't touch a real
 * reader or payment.
 */
class SimulateKioskPaymentCommand extends Command
{
    protected $signature = 'arcane:kiosk-simulate-payment {order : KioskOrder id or reference}';

    protected $description = 'Test mode only — simulates a card being tapped on the configured Terminal reader for a pending kiosk order';

    public function handle(StripeTerminalClient $stripe): int
    {
        $secret = (string) config('services.stripe.secret');

        if (str_starts_with($secret, 'sk_live_')) {
            $this->error('Refusing to run — STRIPE_SECRET looks like a live key.');

            return self::FAILURE;
        }

        $identifier = $this->argument('order');
        $order = is_numeric($identifier)
            ? KioskOrder::find($identifier)
            : KioskOrder::where('reference', $identifier)->first();

        if (! $order) {
            $this->error("No kiosk order found for '{$identifier}'.");

            return self::FAILURE;
        }

        if ($order->status === 'paid') {
            $this->info("Order {$order->reference} is already paid.");

            return self::SUCCESS;
        }

        if (! $order->stripe_payment_intent_id) {
            $this->error("Order {$order->reference} has no PaymentIntent yet — start checkout on the kiosk first.");

            return self::FAILURE;
        }

        try {
            $reader = $stripe->simulateCardPresentment();
        } catch (\Throwable $e) {
            $this->error('Stripe API call failed: '.$e->getMessage());

            return self::FAILURE;
        }

        $this->info("Simulated card presentment for order {$order->reference} — reader action status: {$reader->action->status}.");
        $this->line('Poll the kiosk (or run `stripe listen`) to see it finalize.');

        return self::SUCCESS;
    }
}
