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

    /** Marks the card/pack sold. No-op if already sold — safe to call on a rescan. */
    public function redeem(CardInventory $card, ?int $byUserId): void
    {
        if ($card->status === 'sold') {
            return;
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
    }
}
