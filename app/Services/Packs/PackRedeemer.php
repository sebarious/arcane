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
     * rescan. Returns whether this call actually redeemed it (false means it
     * was already sold beforehand), so callers can tell a fresh confirmation
     * apart from a repeat scan.
     */
    public function redeem(CardInventory $card, ?int $byUserId): bool
    {
        if ($card->status === 'sold') {
            return false;
        }

        DB::transaction(function () use ($card, $byUserId) {
            $card->update([
                'status' => 'sold',
                'delisted_at' => now(),
                'delisted_by_user_id' => $byUserId,
            ]);

            $card->pack?->update([
                'status' => 'sold',
                'sold_at' => now(),
            ]);
        });

        return true;
    }
}
