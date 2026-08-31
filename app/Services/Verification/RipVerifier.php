<?php

namespace App\Services\Verification;

use App\Models\Rip;
use App\Services\Rips\RipDrawer;
use Illuminate\Support\Facades\Storage;

/**
 * Replays a rip's draw against its frozen snapshot and checks it against
 * what was actually delivered — same shape/purpose as BatchVerifier, scaled
 * down to a single card. Calls RipDrawer::pickBand() (the exact same pure
 * function the real draw used) rather than a second copy of that logic.
 *
 * @return array{
 *     available: bool,
 *     reason?: string,
 *     hash_matches?: bool,
 *     band_matches?: bool,
 *     card_matches?: bool,
 *     checked_at?: string,
 * }
 */
class RipVerifier
{
    public function verify(Rip $rip): array
    {
        if (! $rip->isOpened() || ! $rip->verification_snapshot_path) {
            return [
                'available' => false,
                'reason' => 'This pack has not been opened yet — there is nothing to verify until it is.',
            ];
        }

        if (! Storage::disk('local')->exists($rip->verification_snapshot_path)) {
            return [
                'available' => false,
                'reason' => 'Verification data for this pack could not be found.',
            ];
        }

        $snapshot = json_decode(Storage::disk('local')->get($rip->verification_snapshot_path), true);

        $hashMatches = hash('sha256', $rip->verification_seed) === $rip->verification_hash;

        $rng = new SeededRandom($rip->verification_seed);

        $replayedBand = RipDrawer::pickBand($snapshot['band_odds'], $rng);
        $bandMatches = $replayedBand === $snapshot['drawn_band'];

        $poolIds = $snapshot['pool_ids'];
        $index = (int) floor($rng->nextFloat() * count($poolIds));
        $replayedCardId = $poolIds[$index] ?? null;
        $cardMatches = $replayedCardId === $snapshot['chosen_id'] && $snapshot['chosen_id'] === $rip->card_inventory_id;

        return [
            'available' => true,
            'hash_matches' => $hashMatches,
            'band_matches' => $bandMatches,
            'card_matches' => $cardMatches,
            'checked_at' => now()->toIso8601String(),
        ];
    }
}
