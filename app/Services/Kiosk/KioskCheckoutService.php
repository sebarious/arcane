<?php

namespace App\Services\Kiosk;

use App\Exceptions\Kiosk\BasketItemsUnavailableException;
use App\Models\CardInventory;
use App\Models\KioskOrder;
use App\Models\KioskOrderItem;
use App\Services\Batches\PickingSheetGenerator;
use App\Services\Stripe\StripeTerminalClient;
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
        return (int) round(($card->market_value_pence ?? 0) * (float) config('kiosk.markup_multiplier'));
    }

    /** The shared card shape search/browse/basket responses send to the kiosk frontend. */
    public function present(CardInventory $card): array
    {
        return [
            'id' => $card->id,
            'card_name' => $card->card_name,
            'set_name' => $card->set_name,
            'card_number' => $card->card_number,
            'rarity' => $card->rarity_band ? ucfirst($card->rarity_band) : null,
            'image_url' => $card->image_url,
            'price_pence' => $this->priceFor($card),
        ];
    }

    /**
     * Starts checkout for a session's basket: re-verifies every card is still
     * genuinely held by this session (extending the hold to cover payment
     * processing), snapshots pricing into order items, creates the Stripe
     * PaymentIntent, and tells the reader to collect it.
     *
     * @param  int[]  $cardInventoryIds
     *
     * @throws BasketItemsUnavailableException if any basket item's hold has lapsed and been claimed elsewhere
     */
    public function startCheckout(array $cardInventoryIds, string $sessionToken): KioskOrder
    {
        $this->basket->extend($cardInventoryIds, $sessionToken);

        $held = $this->basket->stillHeld($cardInventoryIds, $sessionToken);
        $missing = array_values(array_diff($cardInventoryIds, $held));

        if (! empty($missing)) {
            throw new BasketItemsUnavailableException($missing);
        }

        return DB::transaction(function () use ($cardInventoryIds) {
            $cards = CardInventory::whereIn('id', $cardInventoryIds)->get();

            $order = KioskOrder::create([
                'reference' => KioskOrder::nextReference(),
                'status' => 'pending_payment',
            ]);

            $total = 0;

            foreach ($cards as $card) {
                $unitPrice = $this->priceFor($card);
                $total += $unitPrice;

                KioskOrderItem::create([
                    'kiosk_order_id' => $order->id,
                    'card_inventory_id' => $card->id,
                    'card_name' => $card->card_name,
                    'set_name' => $card->set_name,
                    'card_number' => $card->card_number,
                    'rarity' => $card->rarity_band ? ucfirst($card->rarity_band) : null,
                    'market_value_pence' => $card->market_value_pence,
                    'unit_price_pence' => $unitPrice,
                ]);
            }

            $order->update(['total_pence' => $total]);

            $paymentIntent = $this->stripe->createPaymentIntent($total);
            $order->update(['stripe_payment_intent_id' => $paymentIntent->id]);

            $this->stripe->processPaymentIntentOnReader($paymentIntent->id);

            return $order;
        });
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
