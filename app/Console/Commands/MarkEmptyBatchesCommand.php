<?php

namespace App\Console\Commands;

use App\Models\Batch;
use Illuminate\Console\Command;

/**
 * Stamps emptied_at the first time a batch's packs are all sold, rather than
 * computing "is it empty" on every storefront request. Batch::
 * scopeVisibleOnStorefront reads this timestamp to give a sold-out batch an
 * hour of visibility before it drops off the Card Lists page and its store's
 * profile — someone mid-checkout on the last pack shouldn't have it vanish
 * from under them.
 */
class MarkEmptyBatchesCommand extends Command
{
    protected $signature = 'arcane:mark-empty-batches';

    protected $description = 'Stamp emptied_at on batches whose packs have all sold';

    public function handle(): int
    {
        $candidates = Batch::query()
            ->whereIn('status', ['committed', 'dispatched'])
            ->whereNull('merged_into_batch_id')
            ->whereNull('emptied_at')
            ->where('pack_count', '>', 0)
            ->withCount(['packs as sold_packs_count' => function ($query) {
                $query->where('status', 'sold');
            }])
            ->get()
            ->filter(fn (Batch $batch) => $batch->sold_packs_count === $batch->pack_count);

        if ($candidates->isEmpty()) {
            $this->info('No newly-empty batches.');

            return self::SUCCESS;
        }

        foreach ($candidates as $batch) {
            // The last pack's sold_at, not now() — the hour of grace should
            // count from when the batch actually emptied, not from whenever
            // this command next happens to run.
            $lastSoldAt = $batch->packs()->max('sold_at');

            $batch->update(['emptied_at' => $lastSoldAt ?? now()]);
        }

        $this->info("Marked {$candidates->count()} batch(es) empty.");

        return self::SUCCESS;
    }
}
