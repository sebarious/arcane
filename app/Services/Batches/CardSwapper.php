<?php

namespace App\Services\Batches;

use App\Models\CardInventory;
use App\Models\Pack;
use App\Models\User;
use App\Support\Money;
use Illuminate\Support\Facades\DB;

/**
 * Substitutes a different card into a pack when the one it was allocated
 * turns out to be physically missing during warehouse picking. Deliberately
 * never touches BatchVerifier/CandidateSelector/the verification snapshot —
 * those are checked against a frozen record of the original generation, not
 * live Pack/CardInventory state, so a swap here can't affect "provably
 * fair" (see BatchVerifier's docblock — the same property batch merges
 * already rely on).
 *
 * The replacement must match the band the outgoing card is being replaced
 * *for* — normally that's just the outgoing card's own current rarity_band,
 * but an 'in_stock' removal (the UI calls this "price changed") explicitly
 * targets a different band on purpose (see $targetBand below): the
 * storefront's displayed pull odds are
 * a live count of sealed packs' cards grouped by rarity_band (see
 * BatchListController), so keeping the replacement pinned to the band the
 * slot actually needs — not whatever the outgoing card's live band now says
 * — is what keeps those odds correct, not what breaks them.
 */
class CardSwapper
{
    // Once dispatched/completed the physical stock has left the building —
    // nothing left to swap. 'draft' has no packs yet.
    protected const ELIGIBLE_BATCH_STATUSES = ['pending_review', 'awaiting_payment', 'committed'];

    // What actually happened to the outgoing card — distinct because they mean
    // different things for stock accounting: 'written_off' is a loss (lost,
    // damaged, never findable), 'sold' means it legitimately left inventory
    // via a sale outside the normal pack-redemption flow (e.g. sold in person
    // at the shop before staff realised it was already earmarked for a pack).
    // Neither books new revenue here — same as a normal pack sale, the
    // CardInventory status change is the record, not a new Invoice line.
    // 'in_stock' is different again: nothing was lost or sold, the card's
    // price just moved it into a different band than this slot needs, so it
    // goes straight back into the live pool for a future batch to pick up.
    protected const REMOVAL_STATUSES = ['written_off', 'sold', 'in_stock'];

    /**
     * @param  ?string  $targetBand  Only meaningful (and required) when
     *                               $removalStatus is 'in_stock' — the band this
     *                               slot actually needs, since the outgoing
     *                               card's own rarity_band is exactly what a
     *                               price change just moved away from it.
     *                               Ignored for every other removal status,
     *                               which instead match the outgoing card's
     *                               current band, as before.
     *
     * @throws \RuntimeException if the pack/batch isn't in a swappable state, the
     *                           removal status is invalid, or the replacement isn't
     *                           genuinely available or doesn't match
     */
    public function swap(Pack $pack, CardInventory $replacement, string $removalStatus, string $reason, ?int $byUserId, ?string $targetBand = null): void
    {
        if (! in_array($removalStatus, self::REMOVAL_STATUSES, true)) {
            throw new \RuntimeException("Invalid removal reason: {$removalStatus}.");
        }

        if ($removalStatus === 'in_stock' && ! $targetBand) {
            throw new \RuntimeException('Pick the tier this slot needs before choosing a replacement.');
        }

        $batch = $pack->batch;

        if (! in_array($batch->status, self::ELIGIBLE_BATCH_STATUSES, true)) {
            throw new \RuntimeException("Batch {$batch->reference} isn't in a status where cards can be swapped ({$batch->status}).");
        }

        if ($pack->status !== 'sealed') {
            throw new \RuntimeException("Pack #{$pack->sequence_no} has already been sold — its card can't be swapped.");
        }

        $oldCard = CardInventory::where('pack_id', $pack->id)->first();

        if (! $oldCard) {
            throw new \RuntimeException("Pack #{$pack->sequence_no} has no card currently assigned to swap out.");
        }

        if ($replacement->id === $oldCard->id) {
            throw new \RuntimeException("Pick a different card — that's already the card assigned to that pack.");
        }

        if (! CardInventory::available()->whereKey($replacement->id)->exists()) {
            throw new \RuntimeException("{$replacement->card_name} is no longer available — it may already be allocated, sold, or reserved.");
        }

        $requiredBand = $removalStatus === 'in_stock' ? $targetBand : $oldCard->rarity_band;

        if ($replacement->rarity_band !== $requiredBand) {
            throw new \RuntimeException("Replacement must be {$requiredBand} band — a mismatched band would change this batch's displayed pull odds.");
        }

        if ($replacement->game !== $batch->game) {
            throw new \RuntimeException("Replacement card must be a {$batch->game->label()} card.");
        }

        DB::transaction(function () use ($batch, $pack, $oldCard, $replacement, $removalStatus, $requiredBand, $reason, $byUserId) {
            $allocatedSalePrice = $oldCard->allocated_sale_price_pence;
            $cardMargin = $oldCard->margin_pence;

            // A price-changed card isn't a loss or a removal from sale — it's
            // going straight back into the live pool — so unlike written_off/sold
            // it doesn't get stamped delisted.
            $isPriceChange = $removalStatus === 'in_stock';

            $oldCard->update([
                'pack_id' => null,
                'status' => $removalStatus,
                'qr_token' => null,
                'allocated_sale_price_pence' => null,
                'margin_pence' => null,
                'picked_at' => null,
                'delisted_at' => $isPriceChange ? null : now(),
                'delisted_by_user_id' => $isPriceChange ? null : $byUserId,
            ]);

            $replacement->update([
                'pack_id' => $pack->id,
                'status' => 'allocated',
                'qr_token' => CardInventory::generateQrToken(),
                'allocated_sale_price_pence' => $allocatedSalePrice,
                'margin_pence' => $cardMargin,
            ]);

            $newTotalCost = $batch->total_cost_pence + (($replacement->cost_pence ?? 0) - ($oldCard->cost_pence ?? 0));
            $newTotalMarket = $batch->total_market_value_pence + (($replacement->market_value_pence ?? 0) - ($oldCard->market_value_pence ?? 0));
            $newMargin = $batch->sale_price_pence - $newTotalCost;

            $who = $byUserId ? (User::find($byUserId)?->name ?? "user #{$byUserId}") : 'system';
            $removalLabel = match ($removalStatus) {
                'sold' => 'sold elsewhere',
                'written_off' => 'missing/written off',
                'in_stock' => "price changed to {$oldCard->rarity_band} — returned to stock",
            };
            $note = sprintf(
                '[%s] Card swap by %s: %s (#%d, %s) marked %s, replaced with %s (%s). Reason: %s',
                now()->format('d M Y H:i'),
                $who,
                $oldCard->card_name,
                $pack->sequence_no,
                $requiredBand,
                $removalLabel,
                $replacement->card_name,
                $requiredBand,
                $reason,
            );

            $batch->update([
                'total_cost_pence' => $newTotalCost,
                'total_market_value_pence' => $newTotalMarket,
                'margin_pence' => $newMargin,
                'margin_scheme_vat_pence' => Money::marginSchemeVat($newMargin),
                'top_card_1_id' => $batch->top_card_1_id === $oldCard->id ? $replacement->id : $batch->top_card_1_id,
                'top_card_2_id' => $batch->top_card_2_id === $oldCard->id ? $replacement->id : $batch->top_card_2_id,
                'admin_notes' => trim(($batch->admin_notes ? $batch->admin_notes."\n" : '').$note),
            ]);
        });
    }
}
