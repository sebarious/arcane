<?php

namespace App\Services\Packs;

use App\Models\CardInventory;
use Illuminate\Support\Facades\DB;

class PackRedeemer
{
    // Only a genuinely-shipped batch can have a real customer opening a real
    // pack — 'completed' included since that's the terminal state a
    // dispatched batch eventually reaches, not a separate earlier stage.
    protected const REDEEMABLE_BATCH_STATUSES = ['dispatched', 'completed'];

    public function findByToken(string $token): ?CardInventory
    {
        return CardInventory::with('pack.batch.store')
            ->where('qr_token', $token)
            ->first();
    }

    /**
     * Whether this card's batch has actually shipped — scanning before then
     * (e.g. staff spot-checking a freshly printed QR sheet still sitting in
     * the warehouse) should never be able to consume a pack. This is the fix
     * for exactly that: packs turning up "sold" in clusters, at the same
     * moment, that no customer ever bought — the QR sheet gets handled and
     * checked right around when a batch is sent to warehouse, well before
     * it's dispatched.
     */
    public function isEligibleForRedemption(CardInventory $card): bool
    {
        $batch = $card->pack?->batch;

        return $batch && in_array($batch->status, self::REDEEMABLE_BATCH_STATUSES, true);
    }

    /**
     * Marks the card/pack sold. No-op if both are already sold — safe to
     * call on a rescan. Returns whether this call confirmed or repaired
     * anything, so callers can tell "meaningfully handled just now" apart
     * from "genuinely nothing to do."
     *
     * Checks the card and pack independently rather than short-circuiting on
     * the card alone: a card can end up marked sold while its pack didn't
     * (seen in production — cause not fully pinned down, but the two updates
     * aren't always transactional at every call site). A rescan that hits
     * that drift still counts as confirming — the customer never actually
     * saw a successful confirmation the first time, so a QR-scan caller
     * should show them the same "you're good" message as a fresh scan, not
     * "already sold" (which reads as someone else having claimed it).
     *
     * Callers should check isEligibleForRedemption() first so they can show
     * an accurate message — this repeats that same check as a safety net
     * (fails to a no-op, same as "nothing to do") in case one doesn't.
     */
    public function redeem(CardInventory $card, ?int $byUserId): bool
    {
        if (! $this->isEligibleForRedemption($card)) {
            return false;
        }

        $pack = $card->pack;
        $cardAlreadySold = $card->status === 'sold';
        $packAlreadySold = ! $pack || $pack->status === 'sold';

        if ($cardAlreadySold && $packAlreadySold) {
            return false;
        }

        DB::transaction(function () use ($card, $pack, $byUserId, $cardAlreadySold, $packAlreadySold) {
            if (! $cardAlreadySold) {
                $card->update([
                    'status' => 'sold',
                    'delisted_at' => now(),
                    'delisted_by_user_id' => $byUserId,
                ]);
            }

            if (! $packAlreadySold) {
                $pack->update([
                    'status' => 'sold',
                    'sold_at' => now(),
                ]);
            }
        });

        return true;
    }
}
