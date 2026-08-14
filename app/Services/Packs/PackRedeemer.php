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
     * Marks the card/pack sold. No-op if already sold — safe to call on a
     * rescan. Returns whether this call actually redeemed the *card* (false
     * means it was already sold beforehand), so callers can tell a fresh
     * confirmation apart from a repeat scan.
     *
     * Checks the card and pack independently rather than short-circuiting on
     * the card alone: a card can end up marked sold while its pack didn't
     * (seen in production — cause not fully pinned down, but the two updates
     * aren't always transactional at every call site). A rescan is the
     * natural point to self-heal that drift, since it's already re-checking
     * both rows anyway.
     */
    public function redeem(CardInventory $card, ?int $byUserId): bool
    {
        $pack = $card->pack;
        $cardAlreadySold = $card->status === 'sold';

        if ($cardAlreadySold && (! $pack || $pack->status === 'sold')) {
            return false;
        }

        DB::transaction(function () use ($card, $pack, $byUserId, $cardAlreadySold) {
            if (! $cardAlreadySold) {
                $card->update([
                    'status' => 'sold',
                    'delisted_at' => now(),
                    'delisted_by_user_id' => $byUserId,
                ]);
            }

            if ($pack && $pack->status !== 'sold') {
                $pack->update([
                    'status' => 'sold',
                    'sold_at' => now(),
                ]);
            }
        });

        return ! $cardAlreadySold;
    }
}
