<?php

namespace App\Services\Batches;

use App\Models\Batch;
use Illuminate\Support\Facades\DB;

class BatchMerger
{
  // "Generated and invoiced" batches only — not draft (no invoice yet) or cancelled.
  protected const ELIGIBLE_STATUSES = ['committed', 'dispatched'];

  /**
   * Move a batch's remaining sealed (unsold) packs into another batch, flagging the
   * source as merged. Already-sold packs stay on the source batch — its invoice and
   * financial totals are untouched either way, this is purely a pack/pool reorganization
   * so a depleted batch's leftover stock can join a fresher, more attractive pool.
   *
   * @throws \RuntimeException on any validation failure.
   */
  public function merge(Batch $source, Batch $target): void
  {
    if ($source->id === $target->id) {
      throw new \RuntimeException('A batch cannot be merged into itself.');
    }

    if ($source->isMerged()) {
      throw new \RuntimeException("Batch {$source->reference} has already been merged into another batch.");
    }

    if ($target->isMerged()) {
      throw new \RuntimeException("Batch {$target->reference} has itself been merged elsewhere — merge into its destination instead.");
    }

    if ($source->store_id !== $target->store_id) {
      throw new \RuntimeException('Batches must belong to the same store to be merged.');
    }

    if (! in_array($source->status, self::ELIGIBLE_STATUSES, true)) {
      throw new \RuntimeException("Batch {$source->reference} isn't in a mergeable status ({$source->status}).");
    }

    if (! in_array($target->status, self::ELIGIBLE_STATUSES, true)) {
      throw new \RuntimeException("Batch {$target->reference} isn't in a mergeable status ({$target->status}).");
    }

    DB::transaction(function () use ($source, $target) {
      $sealedPacks = $source->packs()->where('status', 'sealed')->orderBy('sequence_no')->get();

      if ($sealedPacks->isEmpty()) {
        throw new \RuntimeException("Batch {$source->reference} has no remaining sealed packs to merge.");
      }

      // Packs carry a unique (batch_id, sequence_no) — renumber the incoming ones
      // to continue after whatever the target batch already has.
      $nextSequence = ($target->packs()->max('sequence_no') ?? 0) + 1;

      foreach ($sealedPacks as $pack) {
        $pack->update([
          'batch_id'    => $target->id,
          'sequence_no' => $nextSequence++,
        ]);
      }

      $movedCount = $sealedPacks->count();

      $target->update([
        'pack_count' => $target->pack_count + $movedCount,
      ]);

      $source->update([
        'pack_count'           => $source->pack_count - $movedCount,
        'merged_into_batch_id' => $target->id,
        'merged_at'            => now(),
      ]);
    });
  }
}
