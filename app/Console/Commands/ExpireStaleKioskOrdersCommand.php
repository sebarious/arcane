<?php

namespace App\Console\Commands;

use App\Models\KioskOrder;
use App\Services\Stripe\StripeTerminalClient;
use Illuminate\Console\Command;

/**
 * Tidies up kiosk orders whose basket() hold expired without payment
 * completing (customer walked away, card declined and never retried, ...).
 * Doesn't need to touch card_inventory itself — reserved_until expiring is
 * already what makes those cards available again everywhere else; this is
 * purely so abandoned orders don't sit in the admin queue as "pending"
 * forever.
 */
class ExpireStaleKioskOrdersCommand extends Command
{
    protected $signature = 'arcane:expire-kiosk-orders';

    protected $description = 'Mark stale pending kiosk orders as expired';

    public function handle(StripeTerminalClient $stripe): int
    {
        // A buffer past the reservation window — an order isn't stale purely
        // because the hold time elapsed, only once there's clearly no
        // in-progress checkout still running against it.
        $cutoff = now()->subMinutes(((int) config('kiosk.reservation_minutes')) + 5);

        $stale = KioskOrder::where('status', 'pending_payment')
            ->where('created_at', '<', $cutoff)
            ->get();

        if ($stale->isEmpty()) {
            $this->info('No stale kiosk orders.');

            return self::SUCCESS;
        }

        foreach ($stale as $order) {
            $order->update(['status' => 'expired']);
        }

        // Best-effort — if the reader is mid-action on one of these, clear it
        // so it doesn't block the next customer. A failure here isn't fatal;
        // the reader also self-resets after its own timeout.
        try {
            $stripe->cancelReaderAction();
        } catch (\Throwable) {
            // Nothing to clean up, or the reader wasn't in an action — fine either way.
        }

        $this->info("Expired {$stale->count()} stale kiosk order(s).");

        return self::SUCCESS;
    }
}
