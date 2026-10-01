<?php

namespace App\Console\Commands;

use App\Models\KioskOrder;
use App\Services\Kiosk\KioskOpenOrderService;
use Illuminate\Console\Command;

/**
 * Clears out the previous day's uncollected open orders overnight.
 *
 * An open order is a shopping list someone built on the catalogue tablet and
 * was meant to carry to the counter. One still sitting there the next morning
 * was abandoned, and leaving it would mean staff working a queue full of
 * stale lists whose prices and availability have both moved on.
 *
 * Only 'open' is swept — a 'collected' order has been through the till and is
 * kept as the audit trail of what was asked for.
 */
class PurgeOpenKioskOrdersCommand extends Command
{
    protected $signature = 'arcane:purge-open-kiosk-orders';

    protected $description = 'Delete uncollected open kiosk orders left over from previous days';

    public function handle(KioskOpenOrderService $openOrders): int
    {
        // Anything from before today, rather than "older than 24h": the point
        // is that the shop closed and nobody collected it, so the boundary is
        // the trading day, not an elapsed duration.
        $stale = KioskOrder::where('status', KioskOpenOrderService::STATUS_OPEN)
            ->where('created_at', '<', now()->startOfDay())
            ->get();

        foreach ($stale as $order) {
            $openOrders->discard($order);
        }

        $this->info("Purged {$stale->count()} uncollected open order(s).");

        return self::SUCCESS;
    }
}
