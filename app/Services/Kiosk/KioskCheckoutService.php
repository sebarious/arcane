<?php

namespace App\Services\Kiosk;

use App\Exceptions\Kiosk\BasketItemsUnavailableException;
use App\Models\CardInventory;
use App\Models\KioskOrder;
use App\Models\KioskOrderItem;
use App\Services\Batches\PickingSheetGenerator;
use App\Services\Stripe\StripeTerminalClient;
use App\Support\Money;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class KioskCheckoutService
{
    public function __construct(
        private KioskBasketService $basket,
        private StripeTerminalClient $stripe,
        private PickingSheetGenerator $pickingSheetGenerator,
    ) {}

    public function priceFor(CardInventory $card): int
    {
        $marked = ($card->market_value_pence ?? 0) * (float) config('kiosk.markup_multiplier');
        $step = max(1, (int) config('kiosk.price_rounding_pence'));

        return (int) (ceil($marked / $step) * $step);
    }

    /** The shared card shape search/browse/basket responses send to the kiosk frontend. */
    public function present(CardInventory $card): array
    {
        return [
            'id' => $card->id,
            'card_name' => $card->card_name,
            'set_name' => $card->set_name,
            'card_number' => $card->card_number,
            'rarity' => $card->rarity_band ? ucfirst($card->rarity_band) : 'Unbanded',
            'image_url' => $card->image_url,
            'price_pence' => $this->priceFor($card),
            'product_badges' => $card->product_badges,
        ];
    }

    /**
     * What a basket is worth, in one place — the basket endpoint and checkout
     * both go through this, so what the customer is shown and what the reader
     * asks for can't drift apart.
     *
     * @param  Collection<int, CardInventory>  $cards
     * @param  array<int, array{id: string, label: string, price_pence: int}>  $customLines
     * @param  array{type: string, value: int}|null  $discount
     * @return array{data: array, custom_lines: array, subtotal_pence: int, discount: ?array, discount_pence: int, total_pence: int}
     */
    public function summarise(Collection $cards, array $customLines = [], ?array $discount = null): array
    {
        $items = $cards->map(fn (CardInventory $card) => $this->present($card))->values();
        $subtotal = (int) $items->sum('price_pence') + (int) collect($customLines)->sum('price_pence');
        $discountPence = $this->discountFor($subtotal, $discount);

        return [
            'data' => $items->all(),
            'custom_lines' => array_values($customLines),
            'subtotal_pence' => $subtotal,
            'discount' => $discount,
            'discount_pence' => $discountPence,
            'total_pence' => max(0, $subtotal - $discountPence),
        ];
    }

    /**
     * @param  array{type: string, value: int}|null  $discount  value is a percentage for 'percent', pence for 'fixed'
     */
    public function discountFor(int $subtotalPence, ?array $discount): int
    {
        if (! $discount || $subtotalPence <= 0) {
            return 0;
        }

        $value = max(0, (int) ($discount['value'] ?? 0));

        $amount = ($discount['type'] ?? null) === 'percent'
            ? (int) round($subtotalPence * min(100, $value) / 100)
            : $value;

        // Never worth more than the basket — £20 off a £5 basket is £5 off,
        // not £15 owed back.
        return min($amount, $subtotalPence);
    }

    /**
     * Starts checkout for a session's basket: re-verifies every card is still
     * genuinely held by this session (extending the hold to cover payment
     * processing), snapshots pricing into order items, creates the Stripe
     * PaymentIntent, and tells the reader to collect it.
     *
     * @param  int[]  $cardInventoryIds
     * @param  array<int, array{id: string, label: string, price_pence: int}>  $customLines
     * @param  array{type: string, value: int}|null  $discount
     *
     * @throws BasketItemsUnavailableException if any basket item's hold has lapsed and been claimed elsewhere
     * @throws \RuntimeException if the discounted total is below what Stripe will take
     */
    public function startCheckout(array $cardInventoryIds, string $sessionToken, array $customLines = [], ?array $discount = null): KioskOrder
    {
        $this->basket->extend($cardInventoryIds, $sessionToken);

        $held = $this->basket->stillHeld($cardInventoryIds, $sessionToken);
        $missing = array_values(array_diff($cardInventoryIds, $held));

        if (! empty($missing)) {
            throw new BasketItemsUnavailableException($missing);
        }

        $summary = $this->summarise(
            CardInventory::whereIn('id', $cardInventoryIds)->get(),
            $customLines,
            $discount,
        );

        // Stripe rejects anything under its per-currency floor, so catch it
        // here with something the operator can act on rather than letting the
        // reader throw an opaque error at the customer.
        $minimum = (int) config('kiosk.minimum_charge_pence');

        if ($summary['total_pence'] < $minimum) {
            throw new \RuntimeException(sprintf(
                'That comes to %s, which is below the %s minimum a card payment can take. Reduce the discount, or take this one at the till.',
                Money::format($summary['total_pence']),
                Money::format($minimum),
            ));
        }

        return DB::transaction(function () use ($cardInventoryIds, $customLines, $discount, $summary) {
            $cards = CardInventory::whereIn('id', $cardInventoryIds)->get();

            $order = KioskOrder::create([
                'reference' => KioskOrder::nextReference(),
                'status' => 'pending_payment',
            ]);

            foreach ($cards as $card) {
                KioskOrderItem::create([
                    'kiosk_order_id' => $order->id,
                    'card_inventory_id' => $card->id,
                    'card_name' => $card->card_name,
                    'set_name' => $card->set_name,
                    'card_number' => $card->card_number,
                    'rarity' => $card->rarity_band ? ucfirst($card->rarity_band) : 'Unbanded',
                    'market_value_pence' => $card->market_value_pence,
                    'unit_price_pence' => $this->priceFor($card),
                ]);
            }

            // Manual lines are ordinary order items with no inventory behind
            // them — nothing to mark sold or pick, but they belong on the
            // receipt and in the takings like everything else.
            foreach ($customLines as $line) {
                KioskOrderItem::create([
                    'kiosk_order_id' => $order->id,
                    'card_inventory_id' => null,
                    'card_name' => $line['label'],
                    'unit_price_pence' => (int) $line['price_pence'],
                ]);
            }

            $order->update([
                'subtotal_pence' => $summary['subtotal_pence'],
                'discount_type' => $discount['type'] ?? null,
                'discount_value' => $discount['value'] ?? null,
                'discount_pence' => $summary['discount_pence'],
                'total_pence' => $summary['total_pence'],
            ]);

            $paymentIntent = $this->stripe->createPaymentIntent($summary['total_pence']);
            $order->update(['stripe_payment_intent_id' => $paymentIntent->id]);

            $this->stripe->processPaymentIntentOnReader($paymentIntent->id);

            return $order;
        });
    }

    /**
     * Customer-initiated abandon from the pay screen: clears the reader
     * prompt, voids the PaymentIntent so a late tap can't charge someone
     * who's already walked away, and marks the order cancelled.
     *
     * Deliberately leaves the basket holds in place, for the same reason a
     * decline does (see the kiosk's backToBasket()) — the cards are still
     * this session's for the rest of the reservation window, so the customer
     * can go straight back and pay again.
     *
     * Returns the order as it actually ended up, which isn't always
     * cancelled: if the card landed in the gap between them pressing cancel
     * and this reaching Stripe, the payment stands and gets finalized rather
     * than written off. Money already taken is never cancelled away.
     */
    public function cancel(KioskOrder $order): KioskOrder
    {
        if ($order->status !== 'pending_payment') {
            return $order;
        }

        // Best-effort, as in ExpireStaleKioskOrdersCommand — the reader may
        // not be mid-action at all, which throws and is fine either way.
        try {
            $this->stripe->cancelReaderAction();
        } catch (\Throwable) {
        }

        if ($order->stripe_payment_intent_id) {
            if ($this->stripe->retrievePaymentIntent($order->stripe_payment_intent_id)->status === 'succeeded') {
                return $this->finalize($order);
            }

            try {
                $this->stripe->cancelPaymentIntent($order->stripe_payment_intent_id);
            } catch (\Throwable) {
                // Most likely it succeeded in the moment between the check
                // above and this call. Re-read rather than assume, so a real
                // payment never gets recorded as a cancellation.
                if ($this->stripe->retrievePaymentIntent($order->stripe_payment_intent_id)->status === 'succeeded') {
                    return $this->finalize($order);
                }
            }
        }

        $order->update(['status' => 'cancelled']);

        return $order->fresh();
    }

    /**
     * Finalizes a paid order — verifies the PaymentIntent server-side (never
     * trust a client-reported success alone), then runs the exact same
     * chaos-storage picking math batches use so every item gets a lot +
     * position, marks the cards sold, and releases their reservation.
     * Idempotent — safe to call from both the kiosk's poll-triggered finalize
     * and the Stripe webhook, whichever gets there first.
     */
    public function finalize(KioskOrder $order): KioskOrder
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

            $cardIds = $order->items()->pluck('card_inventory_id')->filter()->all();

            $targets = CardInventory::whereIn('id', $cardIds)
                ->whereNull('picked_at')
                ->lockForUpdate()
                ->get();

            $sheet = $this->pickingSheetGenerator->pickTargets($targets);

            foreach ($sheet as $lotGroup) {
                foreach ($lotGroup['cards'] as $row) {
                    $order->items()
                        ->where('card_inventory_id', $row['card_inventory_id'])
                        ->update(['lot' => $lotGroup['lot'], 'position' => $row['position']]);
                }
            }

            CardInventory::whereIn('id', $cardIds)->update([
                'status' => 'sold',
                'delisted_at' => now(),
                'reserved_until' => null,
                'reserved_by' => null,
            ]);

            $order->update(['status' => 'paid', 'paid_at' => now()]);
        });

        return $order->fresh();
    }
}
