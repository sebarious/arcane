<?php

namespace App\Services\Batches;

use App\Models\Batch;
use App\Models\CardInventory;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Chaos storage: cards live in a box named after their acquisition_lot,
 * arranged alphabetically (CardInventory::chaosSortKey — name, then set, then
 * id), with no fixed per-card slot. A card's "position" is just its rank in
 * that order among cards still in the box (picked_at IS NULL) — so it has to
 * be computed fresh every time, never stored.
 *
 * Multiple cards from the same order can come from the same lot, and pulling
 * one physically shifts every card after it — so within one lot, each target
 * card's printed position is its raw rank minus how many *other* targets in
 * this same pick (from this same lot) sort before it, since those are already
 * out of the box by the time you get there. It's normal for two adjacent
 * targets to land on the same printed position: pull the first, the box
 * shrinks, and the next card slides into that same spot.
 */
class PickingSheetGenerator
{
    /**
     * @return Collection<int, array{lot: string, cards: Collection}> lots in
     *                                                                alphabetical order, each with its cards in the order to pick them.
     *                                                                Empty if the batch has nothing left to pick. Marks every returned
     *                                                                card picked_at = now() — calling this twice for the same batch
     *                                                                only ever returns what's left, if anything.
     */
    public function generate(Batch $batch): Collection
    {
        return DB::transaction(function () use ($batch) {
            $targets = CardInventory::whereIn('pack_id', $batch->packs()->pluck('id'))
                ->whereNull('picked_at')
                ->with('pack')
                ->lockForUpdate()
                ->get();

            if ($targets->isEmpty()) {
                return collect();
            }

            $sheet = $targets
                ->groupBy(fn (CardInventory $card) => $card->acquisition_lot ?? '(no lot recorded)')
                ->sortKeys()
                ->map(fn (Collection $lotTargets, string $lot) => [
                    'lot' => $lot,
                    'cards' => $this->positionsFor($lot, $lotTargets),
                ])
                ->values();

            CardInventory::whereIn('id', $targets->pluck('id'))->update(['picked_at' => now()]);

            return $sheet;
        });
    }

    private function positionsFor(string $lot, Collection $targets): Collection
    {
        $box = $this->boxQuery($lot)->lockForUpdate()->pluck('id');
        $rawPosition = $box->flip()->map(fn (int $index) => $index + 1);

        return $targets
            ->sortBy(fn (CardInventory $card) => $card->chaosSortKey())
            ->values()
            ->map(fn (CardInventory $card, int $index) => [
                'position' => ($rawPosition[$card->id] ?? 0) - $index,
                'card_name' => $card->card_name,
                'set_name' => $card->set_name,
                'card_number' => $card->card_number,
                'pack_sequence' => $card->pack?->sequence_no,
            ]);
    }

    private function boxQuery(string $lot)
    {
        $query = $lot === '(no lot recorded)'
            ? CardInventory::whereNull('acquisition_lot')
            : CardInventory::where('acquisition_lot', $lot);

        return $query->whereNull('picked_at')
            ->orderByRaw('LOWER(card_name) asc, LOWER(set_name) asc, id asc');
    }
}
