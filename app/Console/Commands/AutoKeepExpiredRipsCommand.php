<?php

namespace App\Console\Commands;

use App\Models\Rip;
use App\Services\Rips\RipCheckoutService;
use Illuminate\Console\Command;

/**
 * A pack left opened-but-undecided for 24 hours is automatically kept —
 * advertised as a disclaimer under the buy buttons on Rips/Show.vue.
 * Reuses RipCheckoutService::decide() rather than duplicating its logic, so
 * an auto-kept pack goes through exactly the same steps a customer clicking
 * "Keep it" would: card marked sold, pulls-feed entry, and the same
 * "needs posting" admin email/notification.
 */
class AutoKeepExpiredRipsCommand extends Command
{
    protected $signature = 'arcane:auto-keep-expired-rips';

    protected $description = 'Automatically mark opened-but-undecided Digital Rips as kept after 24 hours';

    public function handle(RipCheckoutService $checkout): int
    {
        $cutoff = now()->subHours(24);

        $expired = Rip::query()
            ->whereNotNull('opened_at')
            ->whereNull('decision')
            ->where('opened_at', '<=', $cutoff)
            ->get();

        if ($expired->isEmpty()) {
            $this->info('No expired undecided rips.');

            return self::SUCCESS;
        }

        $failed = 0;

        foreach ($expired as $rip) {
            try {
                $checkout->decide($rip, 'kept');
            } catch (\Throwable $e) {
                // Don't let one bad row stop the rest of the batch — log and
                // move on, it'll simply be picked up again next run.
                $failed++;
                $this->error("Rip {$rip->id}: {$e->getMessage()}");
            }
        }

        $this->info(sprintf(
            'Auto-kept %d rip(s)%s.',
            $expired->count() - $failed,
            $failed ? ", {$failed} failed" : ''
        ));

        return self::SUCCESS;
    }
}
