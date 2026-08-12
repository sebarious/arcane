<?php

namespace App\Services\Kiosk;

use App\Models\CardInventory;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * The concurrency-safe half of the kiosk basket — reserving a specific
 * physical card is a real row lock + write, not just client-side state,
 * since two tablets (or a tablet and BatchGenerator) could otherwise both
 * believe they hold the same card. Session-scoped basket membership itself
 * lives in the controller; this only ever deals in specific CardInventory ids.
 *
 * Every hold is stamped with the Laravel session ID that placed it
 * (reserved_by). Without that, if a hold silently expires and a *different*
 * kiosk session grabs the same card, the original session would have no way
 * to tell its checkout is no longer actually holding it — reserved_until
 * alone being in the future doesn't say whose hold it is.
 */
class KioskBasketService
{
    /** Reserves a card for the given session if it's still available. Null if someone/something else already claimed it. */
    public function reserve(int $cardInventoryId, string $sessionToken): ?CardInventory
    {
        return DB::transaction(function () use ($cardInventoryId, $sessionToken) {
            $card = CardInventory::where('id', $cardInventoryId)
                ->available()
                ->lockForUpdate()
                ->first();

            if (! $card) {
                return null;
            }

            $card->update([
                'reserved_until' => $this->holdUntil(),
                'reserved_by' => $sessionToken,
            ]);

            return $card;
        });
    }

    /** Releases a hold this session placed — e.g. removing a card from the basket, or abandoning it. No-op if this session doesn't hold it. */
    public function release(int $cardInventoryId, string $sessionToken): void
    {
        CardInventory::where('id', $cardInventoryId)
            ->where('reserved_by', $sessionToken)
            ->update(['reserved_until' => null, 'reserved_by' => null]);
    }

    /**
     * Bumps the hold on a set of cards this session already reserved — called
     * at checkout so payment processing has room. Only cards still actually
     * held by this session (reserved_by matches, hasn't expired) are
     * extended; anything else is silently skipped — the caller is expected to
     * separately verify every id it asked for came back extended, since a
     * missing one means that card is no longer this session's to buy.
     */
    public function extend(array $cardInventoryIds, string $sessionToken): void
    {
        CardInventory::whereIn('id', $cardInventoryIds)
            ->where('reserved_by', $sessionToken)
            ->where('reserved_until', '>', now())
            ->update(['reserved_until' => $this->holdUntil()]);
    }

    /** Releases every hold this session currently has — the "Clear basket" button. */
    public function releaseAllForSession(string $sessionToken): void
    {
        CardInventory::where('reserved_by', $sessionToken)
            ->update(['reserved_until' => null, 'reserved_by' => null]);
    }

    /** Which of these ids are currently validly held by this session — used to detect basket items that quietly expired. */
    public function stillHeld(array $cardInventoryIds, string $sessionToken): array
    {
        return CardInventory::whereIn('id', $cardInventoryIds)
            ->where('reserved_by', $sessionToken)
            ->where('reserved_until', '>', now())
            ->pluck('id')
            ->all();
    }

    private function holdUntil(): Carbon
    {
        return now()->addMinutes((int) config('kiosk.reservation_minutes'));
    }
}
