<?php

namespace App\Services\Rips;

use App\Models\CardInventory;
use App\Models\Rip;
use App\Models\RipPack;
use App\Services\Verification\SeededRandom;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;

/**
 * The single-card equivalent of BatchGenerator's selection step. A band is
 * picked by weighted random draw against the pack's band_odds, then a card
 * is picked deterministically from that band's currently-available pool —
 * both draws using the rip's own seed (already committed at row-creation
 * time, see Rip::booted()), so the whole thing is replayable by RipVerifier.
 */
class RipDrawer
{
    // Fixed order the cumulative distribution walks in — must stay stable,
    // since it's part of what the seeded draw is deterministic *against*.
    private const BAND_ORDER = ['common', 'rare', 'super', 'legendary', 'mythic'];

    /**
     * Whether every band this pack has nonzero odds for currently has at
     * least one available card across the pack's games — checked at
     * checkout, before payment, so we never take money for a pack that
     * might fail to draw. Does not itself reserve anything (see the
     * `available()` scope's own note on races being rare/acceptable here,
     * same as batch generation's own fail-fast check).
     */
    public function isFulfillable(RipPack $pack): bool
    {
        foreach (self::BAND_ORDER as $band) {
            $odds = (float) ($pack->band_odds[$band] ?? 0);
            if ($odds <= 0) {
                continue;
            }

            $exists = CardInventory::available()
                ->whereIn('game', $pack->games)
                ->where('rarity_band', $band)
                ->exists();

            if (! $exists) {
                return false;
            }
        }

        return true;
    }

    /**
     * Draws a card for this rip — band first, then a specific card within
     * it — and allocates it (card.rip_id, status='allocated'). Writes a
     * verification snapshot of the exact pool the draw was made from so
     * RipVerifier can replay it later.
     *
     * @throws \RuntimeException if the drawn band (or, in the rare case stock
     *                           changed since the checkout-time fulfilability
     *                           check, any band) has nothing available.
     */
    public function draw(Rip $rip): CardInventory
    {
        $pack = $rip->pack;
        $rng = new SeededRandom($rip->verification_seed);

        $band = self::pickBand($pack->band_odds, $rng);

        $pool = CardInventory::available()
            ->whereIn('game', $pack->games)
            ->where('rarity_band', $band)
            ->orderBy('id')
            ->get();

        if ($pool->isEmpty()) {
            throw new \RuntimeException("Rip {$rip->id}: no available {$band} stock at draw time (pack: {$pack->name}).");
        }

        $index = (int) floor($rng->nextFloat() * $pool->count());
        $card = $pool[$index];

        $snapshotPath = $this->writeSnapshot($rip, $band, $pool, $card);

        $card->update([
            'rip_id' => $rip->id,
            'status' => 'allocated',
        ]);

        $rip->update([
            'card_inventory_id' => $card->id,
            'verification_snapshot_path' => $snapshotPath,
        ]);

        return $card;
    }

    /**
     * Pure and stateless (no DB access) so RipVerifier can call this exact
     * same implementation to replay a draw against a frozen snapshot's
     * band_odds, rather than keeping a second copy that could drift.
     *
     * @param  array<string, float>  $bandOdds
     * @return string One of self::BAND_ORDER.
     */
    public static function pickBand(array $bandOdds, SeededRandom $rng): string
    {
        $roll = $rng->nextFloat();
        $cumulative = 0.0;

        foreach (self::BAND_ORDER as $band) {
            $cumulative += (float) ($bandOdds[$band] ?? 0);

            if ($roll < $cumulative) {
                return $band;
            }
        }

        // Odds not summing to exactly 1.0 due to float rounding — fall back
        // to the last band with nonzero odds rather than the literal last
        // entry in BAND_ORDER, which could be a 0%-odds band.
        foreach (array_reverse(self::BAND_ORDER) as $band) {
            if ((float) ($bandOdds[$band] ?? 0) > 0) {
                return $band;
            }
        }

        throw new \RuntimeException('No bands with nonzero odds in the given band_odds.');
    }

    /**
     * Frozen inputs a later verification needs to replay this exact draw —
     * same reasoning as Batch::writeVerificationSnapshot(), scaled down to
     * one card instead of a whole batch's worth.
     */
    private function writeSnapshot(Rip $rip, string $band, Collection $pool, CardInventory $chosen): string
    {
        $path = "rip-verification-snapshots/{$rip->id}.json";

        Storage::disk('local')->put($path, json_encode([
            'band_odds' => $rip->pack->band_odds,
            'games' => $rip->pack->games,
            'drawn_band' => $band,
            'pool_ids' => $pool->pluck('id')->values()->all(),
            'chosen_id' => $chosen->id,
        ], JSON_PRETTY_PRINT));

        return $path;
    }
}
