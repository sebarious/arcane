<?php

namespace App\Services\Verification;

use App\Models\Batch;
use App\Services\Batches\CandidateSelector;
use Illuminate\Support\Facades\Storage;

class BatchVerifier
{
    public function __construct(protected CandidateSelector $selector) {}

    /**
     * Replays the exact same deterministic search BatchGenerator ran at generation
     * time — same seed, same frozen stock pool, same config — and checks the result
     * against the assignment recorded in the snapshot at generation time. Two
     * independent checks: the revealed seed hashes to the ID that was published
     * before generation ran, and replaying the search from that seed reproduces the
     * exact pack→card assignment that was originally generated.
     *
     * Deliberately checked against the snapshot's frozen original_assignment, not
     * the batch's live packs()/CardInventory rows — those can legitimately change
     * after generation (most notably a batch merge, which reassigns a batch's
     * remaining sealed packs to a different batch entirely). A verification proves
     * what was originally generated wasn't predetermined or tampered with; it isn't
     * meant to re-assert itself against unrelated, legitimate reorganizations that
     * happen afterward.
     *
     * @return array{
     *     available: bool,
     *     reason?: string,
     *     hash_matches?: bool,
     *     selection_matches?: bool,
     *     checked_at?: string,
     * }
     */
    public function verify(Batch $batch): array
    {
        if (! $batch->isVerificationRevealed() || ! $batch->verification_snapshot_path) {
            return [
                'available' => false,
                'reason' => 'This batch has not been generated yet — there is nothing to verify until it is.',
            ];
        }

        if (! Storage::disk('local')->exists($batch->verification_snapshot_path)) {
            return [
                'available' => false,
                'reason' => 'Verification data for this batch could not be found.',
            ];
        }

        $snapshot = json_decode(Storage::disk('local')->get($batch->verification_snapshot_path), true);

        $hashMatches = hash('sha256', $batch->verification_seed) === $batch->verification_hash;

        $rng = new SeededRandom($batch->verification_seed);

        $result = $this->selector->select(
            pool: $snapshot['pool'],
            bandDistribution: $snapshot['band_distribution'],
            duplicateLimits: $snapshot['duplicate_limits'],
            thresholds: $snapshot['thresholds'],
            targetSale: $snapshot['target_sale_pence'],
            targetMargin: $snapshot['target_margin'],
            targetValue: $snapshot['target_value_pence'],
            packCount: $snapshot['pack_count'],
            rng: $rng,
            tierDistribution: $snapshot['tier_distribution'] ?? [],
            attempts: $snapshot['attempts'],
        );

        $replayedIds = $result['best']['selected_ids'] ?? [];
        $originalIds = $snapshot['original_assignment'] ?? [];

        return [
            'available' => true,
            'hash_matches' => $hashMatches,
            'selection_matches' => $replayedIds === $originalIds,
            'checked_at' => now()->toIso8601String(),
        ];
    }
}
