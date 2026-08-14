<?php

namespace App\Services\Packs;

use App\Models\CardInventory;
use Illuminate\Support\Facades\DB;

class PackRedeemer
{
    public function findByToken(string $token): ?CardInventory
    {
        return CardInventory::with('pack.batch.store')
            ->where('qr_token', $token)
            ->first();
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
     */
    public function redeem(CardInventory $card, ?int $byUserId): bool
    {
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
