<?php

namespace App\Services\Kiosk;

use App\Models\CardInventory;
use App\Models\KioskOrder;
use App\Models\KioskOrderItem;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * "Open" orders — a shopping list a customer builds for themselves on the
 * browse-only catalogue tablet (/catalogue/kiosk), then pays for at the
 * trade counter.
 *
 * Deliberately reserves nothing. An open order is a request, not a hold: the
 * cards stay fully available to the till, to batch generation and to the next
 * customer until staff actually pull the order up. That is the whole reason
 * an unattended, unauthenticated tablet is allowed to create one — a walk-up
 * customer can't take stock out of circulation just by tapping things. The
 * cost is that an item can be gone by the time staff collect it, which
 * collect() reports rather than quietly dropping.
 */
class KioskOpenOrderService
{
    /** Awaiting collection at the counter. */
    public const STATUS_OPEN = 'open';

    /** Pulled into the till basket; kept for the audit trail, out of the open list. */
    public const STATUS_COLLECTED = 'collected';

    public function __construct(
        private KioskCheckoutService $checkout,
        private KioskBasketService $basket,
    ) {}

    /**
     * @param  int[]  $cardInventoryIds
     *
     * @throws \RuntimeException if nothing in the list is still available
     */
    public function create(array $cardInventoryIds): KioskOrder
    {
        $ids = array_values(array_unique(array_filter(array_map('intval', $cardInventoryIds))));

        // available() rather than a bare lookup: there's no point writing down
        // a request for something already sold. It's only a snapshot — nothing
        // is held (see the class docblock).
        $cards = CardInventory::whereIn('id', $ids)->available()->get();

        if ($cards->isEmpty()) {
            throw new \RuntimeException('None of those cards are still available.');
        }

        $summary = $this->checkout->summarise($cards);

        return DB::transaction(function () use ($cards, $summary) {
            $order = KioskOrder::create([
                'reference' => KioskOrder::nextReference(),
                'status' => self::STATUS_OPEN,
                'subtotal_pence' => $summary['subtotal_pence'],
                'discount_pence' => 0,
                'total_pence' => $summary['total_pence'],
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
                    // Snapshot of the price shown on the tablet, so the figure
                    // the customer was quoted is the figure staff see.
                    'unit_price_pence' => $this->checkout->priceFor($card),
                ]);
            }

            return $order;
        });
    }

    /** Everything still waiting at the counter, oldest first — the queue staff work through. */
    public function open(): Collection
    {
        return KioskOrder::with('items')
            ->where('status', self::STATUS_OPEN)
            ->orderBy('created_at')
            ->get();
    }

    /**
     * Moves an open order into the till's basket: reserves what's still there
     * for this till session, and reports what isn't.
     *
     * Because nothing was held, some items may have sold in the meantime.
     * Those are returned by name so staff can tell the customer exactly what's
     * gone rather than silently handing over a shorter order.
     *
     * @return array{reserved: int[], unavailable: string[]}
     */
    public function collect(KioskOrder $order, string $sessionToken): array
    {
        $reserved = [];
        $unavailable = [];

        foreach ($order->items as $item) {
            if (! $item->card_inventory_id) {
                continue;
            }

            if ($this->basket->reserve($item->card_inventory_id, $sessionToken)) {
                $reserved[] = $item->card_inventory_id;
            } else {
                $unavailable[] = $item->card_name;
            }
        }

        // Out of the open queue either way: staff have dealt with it, and a
        // half-collected order left open would be picked up again later.
        $order->update(['status' => self::STATUS_COLLECTED]);

        return ['reserved' => $reserved, 'unavailable' => $unavailable];
    }

    /** Staff discarding a request the customer walked away from. */
    public function discard(KioskOrder $order): void
    {
        DB::transaction(function () use ($order) {
            $order->items()->delete();
            $order->delete();
        });
    }

    /** Shape the till's open-orders list renders. */
    public function present(KioskOrder $order): array
    {
        return [
            'id' => $order->id,
            'reference' => $order->reference,
            'item_count' => $order->items->count(),
            'total_pence' => $order->total_pence,
            'created_at' => $order->created_at?->toIso8601String(),
            'items' => $order->items->map(fn (KioskOrderItem $item) => [
                'card_name' => $item->card_name,
                'set_name' => $item->set_name,
                'unit_price_pence' => $item->unit_price_pence,
            ])->values()->all(),
        ];
    }
}
