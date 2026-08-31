<?php

namespace App\Services\Rips;

use App\Models\RipWallet;
use App\Models\RipWalletTopup;
use App\Models\User;
use App\Services\Stripe\StripeCheckoutClient;
use Illuminate\Support\Facades\DB;

/**
 * Letting a customer add their own money to their Digital Rips wallet —
 * same pending-payment/finalize shape as RipCheckoutService's pack
 * checkout, just without any packs or draws involved.
 */
class RipWalletTopupService
{
    public const MINIMUM_TOPUP_PENCE = 100; // £1

    public function __construct(
        private StripeCheckoutClient $stripe,
        private RipWalletService $wallet,
    ) {}

    public function startTopup(User $user, int $amountPence): RipWalletTopup
    {
        if ($amountPence < self::MINIMUM_TOPUP_PENCE) {
            throw new \RuntimeException('The minimum top-up is £1.');
        }

        return DB::transaction(function () use ($user, $amountPence) {
            $topup = RipWalletTopup::create([
                'user_id' => $user->id,
                'amount_pence' => $amountPence,
                'status' => 'pending_payment',
            ]);

            $paymentIntent = $this->stripe->createPaymentIntent($amountPence);
            $topup->update(['stripe_payment_intent_id' => $paymentIntent->id]);

            // Not a column — see RipCheckoutService::startCheckout()'s
            // identical note on why this is a transient attribute.
            $topup->setAttribute('stripe_client_secret', $paymentIntent->client_secret);

            return $topup;
        });
    }

    /**
     * Verifies the PaymentIntent server-side and, if genuinely paid, credits
     * the wallet. Idempotent — safe from both the frontend's poll and the
     * Stripe webhook, whichever gets there first.
     */
    public function finalize(RipWalletTopup $topup): RipWalletTopup
    {
        if ($topup->status === 'paid') {
            return $topup;
        }

        $paymentIntent = $this->stripe->retrievePaymentIntent($topup->stripe_payment_intent_id);

        if ($paymentIntent->status !== 'succeeded') {
            return $topup;
        }

        DB::transaction(function () use ($topup) {
            $topup->refresh();

            if ($topup->status === 'paid') {
                return;
            }

            $wallet = $topup->user->ripWallet ?? RipWallet::create(['user_id' => $topup->user_id]);

            $this->wallet->addCredit(
                $wallet,
                $topup->amount_pence,
                'Wallet top-up via card',
                topup: $topup,
            );

            $topup->update(['status' => 'paid', 'paid_at' => now()]);
        });

        return $topup->fresh();
    }
}
