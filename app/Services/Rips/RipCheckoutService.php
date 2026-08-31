<?php

namespace App\Services\Rips;

use App\Mail\RipKeptAdminAlertMail;
use App\Models\CardInventory;
use App\Models\Rip;
use App\Models\RipOrder;
use App\Models\RipPack;
use App\Models\RipWallet;
use App\Models\User;
use App\Services\Banding\RarityBander;
use App\Services\Stripe\StripeCheckoutClient;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class RipCheckoutService
{
    public function __construct(
        private StripeCheckoutClient $stripe,
        private RipDrawer $drawer,
        private RipWalletService $wallet,
        private RipPullFeedService $pullFeed,
    ) {}

    /**
     * The shared first half of both checkout paths (card via Stripe, or
     * instant via wallet balance) — creates the order + one Rip row per pack
     * (seed/hash committed immediately, see Rip::booted()). No card is drawn
     * yet, and no payment has happened yet — the caller wraps this in its
     * own transaction alongside whichever payment step follows.
     *
     * @param  array<int, array{rip_pack_id: int, quantity: int}>  $items
     *
     * @throws \RuntimeException if the basket is empty, a requested pack is
     *                           inactive, or currently can't be fulfilled
     *                           from live stock (see RipDrawer::isFulfillable())
     *                           — refuses to take payment for a pack that
     *                           might fail to draw.
     */
    private function createOrderWithRips(User $user, array $items): RipOrder
    {
        if (empty($items)) {
            throw new \RuntimeException('Your basket is empty.');
        }

        // A "kept" card has to be posted somewhere — required before any
        // pack is bought, not just before deciding to keep one, so it's
        // never a surprise mid-decision. See User::hasShippingAddress().
        if (! $user->hasShippingAddress()) {
            throw new \RuntimeException('Please add your shipping address before buying a pack.');
        }

        $order = RipOrder::create([
            'user_id' => $user->id,
            'reference' => RipOrder::nextReference(),
            'status' => 'pending_payment',
        ]);

        $total = 0;

        foreach ($items as $item) {
            $pack = RipPack::where('status', 'active')->find($item['rip_pack_id']);

            if (! $pack) {
                throw new \RuntimeException('One of the packs in your basket is no longer available.');
            }

            if (! $this->drawer->isFulfillable($pack)) {
                throw new \RuntimeException("{$pack->name} isn't currently available — please check back later.");
            }

            $quantity = max(1, (int) $item['quantity']);

            for ($i = 0; $i < $quantity; $i++) {
                Rip::create([
                    'rip_order_id' => $order->id,
                    'rip_pack_id' => $pack->id,
                    'pack_name' => $pack->name,
                    'price_pence' => $pack->price_pence,
                    'buy_back_percentage' => $pack->buy_back_percentage,
                ]);

                $total += $pack->price_pence;
            }
        }

        $order->update(['total_pence' => $total]);

        return $order;
    }

    /**
     * Starts card checkout — opens a Stripe PaymentIntent for the order
     * total. Draw happens later, in finalize(), once payment is confirmed.
     *
     * @param  array<int, array{rip_pack_id: int, quantity: int}>  $items
     */
    public function startCheckout(User $user, array $items): RipOrder
    {
        return DB::transaction(function () use ($user, $items) {
            $order = $this->createOrderWithRips($user, $items);

            $paymentIntent = $this->stripe->createPaymentIntent($order->total_pence);
            $order->update(['stripe_payment_intent_id' => $paymentIntent->id, 'payment_method' => 'card']);

            // Not a column — client_secret is only ever needed once, right
            // here in the checkout response, to hand to Stripe's Payment
            // Element. Stashed as a transient attribute rather than stored,
            // since Stripe itself is the source of truth for it thereafter.
            $order->setAttribute('stripe_client_secret', $paymentIntent->client_secret);

            return $order;
        });
    }

    /**
     * Pays for the order instantly out of the customer's own wallet balance
     * — no Stripe involved, no polling needed, so the cards are drawn
     * immediately within the same transaction as the debit.
     *
     * @param  array<int, array{rip_pack_id: int, quantity: int}>  $items
     *
     * @throws \RuntimeException if the wallet balance doesn't cover the total
     *                           (rolls back the whole order — nothing is left
     *                           half-created).
     */
    public function payFromWallet(User $user, array $items): RipOrder
    {
        return DB::transaction(function () use ($user, $items) {
            $order = $this->createOrderWithRips($user, $items);

            $wallet = $user->ripWallet ?? RipWallet::create(['user_id' => $user->id]);

            $this->wallet->debitForPurchase(
                $wallet,
                $order->total_pence,
                "Pack purchase — {$order->reference}",
            );

            $order->update(['status' => 'paid', 'payment_method' => 'wallet', 'paid_at' => now()]);

            foreach ($order->rips as $rip) {
                $this->drawer->draw($rip);
            }

            return $order->fresh();
        });
    }

    /**
     * Verifies the PaymentIntent server-side (never trust a client-reported
     * success alone) and, if genuinely paid, draws a card for every rip in
     * the order. Idempotent — safe to call from both the frontend's
     * poll-triggered finalize and the Stripe webhook, whichever gets there
     * first, same shape as KioskCheckoutService::finalize().
     */
    public function finalize(RipOrder $order): RipOrder
    {
        if ($order->status === 'paid') {
            return $order;
        }

        $paymentIntent = $this->stripe->retrievePaymentIntent($order->stripe_payment_intent_id);

        if ($paymentIntent->status !== 'succeeded') {
            return $order;
        }

        DB::transaction(function () use ($order) {
            $order->refresh();

            if ($order->status === 'paid') {
                return;
            }

            foreach ($order->rips as $rip) {
                if (! $rip->isDrawn()) {
                    $this->drawer->draw($rip);
                }
            }

            $order->update(['status' => 'paid', 'paid_at' => now()]);
        });

        return $order->fresh();
    }

    /**
     * The customer triggering the tear-open animation — the card was already
     * determined at finalize() time, this just marks the moment they're
     * allowed to see it. Idempotent.
     */
    public function open(Rip $rip): Rip
    {
        if ($rip->order->status !== 'paid' || ! $rip->isDrawn()) {
            throw new \RuntimeException('This pack isn\'t ready to open yet.');
        }

        if (! $rip->isOpened()) {
            $rip->update(['opened_at' => now()]);
        }

        return $rip->fresh();
    }

    /**
     * @throws \RuntimeException if the rip isn't open yet, or has already been decided
     */
    public function decide(Rip $rip, string $decision): Rip
    {
        if (! in_array($decision, ['kept', 'sold_back'], true)) {
            throw new \InvalidArgumentException("Invalid decision: {$decision}.");
        }

        if (! $rip->isOpened()) {
            throw new \RuntimeException('Open this pack before deciding what to do with it.');
        }

        if ($rip->isDecided()) {
            throw new \RuntimeException('You\'ve already decided on this pack.');
        }

        $rip = DB::transaction(function () use ($rip, $decision) {
            $card = $rip->card;

            if ($decision === 'kept') {
                $card->update(['status' => 'sold', 'delisted_at' => now()]);
                $this->pullFeed->recordKeep($rip, $card);

                $rip->update(['decision' => 'kept', 'decided_at' => now()]);
            } else {
                $soldBackPence = (int) round(($card->market_value_pence ?? 0) * $rip->buy_back_percentage);

                // The original row really was sold once, via this rip —
                // terminal, same as the 'kept' branch above, and its
                // cost_pence stays exactly what Arcane originally paid for
                // it so that acquisition's own accounting is never rewritten.
                $card->update(['status' => 'sold', 'delisted_at' => now(), 'rip_id' => null]);

                // The buy-back is a genuinely new acquisition of the same
                // physical card — a fresh CardInventory row at the price we
                // just paid the customer, not the old (almost always much
                // lower) original cost. Reusing the old row's cost_pence
                // here would silently understate the cost basis on every
                // future batch/rip this card gets drawn into. Mirrors
                // RapidIntake's own CardInventory::create() shape.
                $newCard = CardInventory::create([
                    'condition' => $card->condition,
                    'cost_pence' => $soldBackPence,
                    'acquired_at' => now(),
                    'acquired_from' => "Digital Rip buy-back — {$rip->pack_name} (Rip #{$rip->id})",
                    'acquisition_lot' => 'Digital Rip buy-back',
                    'market_value_pence' => $card->market_value_pence,
                    'market_value_updated_at' => $card->market_value_updated_at,
                    'price_locked' => false,
                    'rarity_band' => app(RarityBander::class)->bandFor($card->market_value_pence),
                    'status' => 'in_stock',
                    'game' => $card->game,
                    // PulseAPI identity fields — same physical card, new inventory lot.
                    'product_id' => $card->product_id,
                    'card_name' => $card->card_name,
                    'card_number' => $card->card_number,
                    'set_id' => $card->set_id,
                    'set_name' => $card->set_name,
                    'series' => $card->series,
                    'release_date' => $card->release_date,
                    'material' => $card->material,
                    'promo_info' => $card->promo_info,
                    'graded_by' => $card->graded_by,
                    'grade' => $card->grade,
                    'rarity' => $card->rarity,
                    'rarity_rank' => $card->rarity_rank,
                    'language' => $card->language,
                    'illustrator' => $card->illustrator,
                    'pokedex_number' => $card->pokedex_number,
                    'image_url' => $card->image_url,
                    'slug' => $card->slug,
                    'synced_at' => $card->synced_at,
                ]);

                $wallet = $rip->order->user->ripWallet ?? RipWallet::create(['user_id' => $rip->order->user_id]);

                $this->wallet->addCredit(
                    $wallet,
                    $soldBackPence,
                    "Sold back — {$rip->pack_name} ({$card->card_name})",
                    rip: $rip,
                );

                $rip->update([
                    'decision' => 'sold_back',
                    'decided_at' => now(),
                    'sold_back_pence' => $soldBackPence,
                ]);
            }

            return $rip->fresh();
        });

        // Outside the transaction — a kept card now needs pulling and
        // posting, and the ops team needs to know that regardless of
        // whether the mailer itself hiccups (never let a notification
        // failure undo an already-committed decision).
        if ($decision === 'kept') {
            $this->notifyAdminsRipNeedsPosting($rip);
        }

        return $rip;
    }

    /**
     * Best-effort, same reasoning as RipPullFeedService::recordKeep() — a
     * mailer/notification hiccup (or even something as basic as the 'admin'
     * role not existing in a given environment) must never surface as a
     * failure on an already-committed keep decision.
     */
    private function notifyAdminsRipNeedsPosting(Rip $rip): void
    {
        try {
            foreach (User::role('admin')->get() as $admin) {
                Mail::to($admin->email)->send(new RipKeptAdminAlertMail($rip));

                Notification::make()
                    ->title('Digital Rip needs posting')
                    ->body("{$rip->order->user->name} kept a card from {$rip->pack_name} — needs pulling and posting.")
                    ->sendToDatabase($admin);
            }
        } catch (\Throwable $e) {
            Log::warning('Failed to notify admins that a Digital Rip needs posting', [
                'rip_id' => $rip->id,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
