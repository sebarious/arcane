<?php

namespace App\Services\Rips;

use App\Models\Batch;
use App\Models\CardInventory;
use App\Models\Pack;
use App\Models\Rip;
use Illuminate\Support\Facades\Log;

/**
 * Makes a kept Digital Rip show up in the homepage's existing "Latest Pulls"
 * feed (HomeController::$recentPulls / app/helpers.php's whats_in_the_pool())
 * with zero changes to that query — by creating a genuine sold Pack under a
 * permanent, singleton pseudo-Batch (no real Store needed: Batch.store_id is
 * already nullable, and PullCard.vue never renders store/batch info anyway).
 * Same singleton-row pattern as Batch::TEST_BATCH_REFERENCE/TestBatchService.
 *
 * Deliberately only ever called for 'kept' decisions — see RipCheckoutService.
 * A sold-back card's status returns to in_stock, and CardInventory::
 * scopeAvailable() requires pack_id IS NULL; permanently occupying a pack_id
 * would wrongly hide that card from every future batch/rip draw. Only a kept
 * card's status is terminal ('sold'), so only that case is safe to attach here.
 */
class RipPullFeedService
{
    public const PSEUDO_BATCH_REFERENCE = 'ARC-DIGITAL-RIPS';

    /**
     * Best-effort — a failure here must never break the actual keep decision
     * it's recording, so this only ever logs, never throws.
     */
    public function recordKeep(Rip $rip, CardInventory $card): void
    {
        try {
            $batch = $this->pseudoBatch();
            $nextSequence = ((int) $batch->packs()->max('sequence_no')) + 1;

            $pack = Pack::create([
                'batch_id' => $batch->id,
                'sequence_no' => $nextSequence,
                'status' => 'sold',
                'sold_at' => now(),
            ]);

            $card->update(['pack_id' => $pack->id]);
        } catch (\Throwable $e) {
            Log::warning('Failed to record digital rip keep in the pulls feed', [
                'rip_id' => $rip->id,
                'card_inventory_id' => $card->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    private function pseudoBatch(): Batch
    {
        return Batch::firstOrCreate(
            ['reference' => self::PSEUDO_BATCH_REFERENCE],
            [
                'store_id' => null,
                'status' => 'committed',
                'pack_count' => 0,
                'total_cost_pence' => 0,
                'total_market_value_pence' => 0,
                'sale_price_pence' => 0,
                'margin_pence' => 0,
                'margin_scheme_vat_pence' => 0,
                'is_test' => false,
            ]
        );
    }
}
