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
     *                                                                only ever returns what's left, if anything. See alreadyPicked()
     *                                                                below for what's already been picked in an earlier run.
     */
    public function generate(Batch $batch): Collection
    {
        return DB::transaction(function () use ($batch) {
            $targets = CardInventory::whereIn('pack_id', $batch->packs()->pluck('id'))
                ->whereNull('picked_at')
                ->with('pack')
                ->lockForUpdate()
                ->get();

            return $this->pickTargets($targets);
        });
    }

    /**
     * The shared core: given a specific, already-loaded set of cards to pick
     * (any source — a batch's packs, a kiosk order's basket, ...), groups them
     * by lot, computes each one's current chaos-storage position, and stamps
     * picked_at on all of them. This is what keeps batch picking sheets and
     * kiosk order fulfilment from ever computing position differently.
     *
     * Must be called from inside a DB::transaction() the caller controls,
     * after locking the target rows (see generate() above) — the box-position
     * query below takes its own lock too, but that only means anything nested
     * inside an already-open transaction.
     *
     * @param  Collection<int, CardInventory>  $targets
     * @return Collection<int, array{lot: string, cards: Collection}>
     */
    public function pickTargets(Collection $targets): Collection
    {
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
    }

    private function positionsFor(string $lot, Collection $targets): Collection
    {
        $box = $this->boxQuery($lot)->lockForUpdate()->pluck('id');
        $rawPosition = $box->flip()->map(fn (int $index) => $index + 1);

        return $targets
            ->sortBy(fn (CardInventory $card) => $card->chaosSortKey())
            ->values()
            ->map(fn (CardInventory $card, int $index) => [
                'card_inventory_id' => $card->id,
                'position' => ($rawPosition[$card->id] ?? 0) - $index,
                'card_name' => $card->card_name,
                'set_name' => $card->set_name,
                'card_number' => $card->card_number,
                // Our own band (common/rare/super/legendary/mythic), not PulseAPI's
                // printed rarity — matches what staff actually use day to day.
                'rarity' => $card->rarity_band ? ucfirst($card->rarity_band) : 'Unbanded',
                // Same-name cards can differ by print variant (e.g. a Stamped or
                // Pokémon Center Fennekin vs. the base print) — without this, staff
                // pulling by name alone can grab the wrong physical copy from the box.
                'product_badges' => $card->product_badges,
                'pack_sequence' => $card->pack?->sequence_no,
            ]);
    }

    /**
     * Everything in this batch that's already been picked (picked_at set) —
     * for a read-only reference section on the sheet alongside whatever
     * generate() returns, so a sheet regenerated after e.g. a CardSwapper
     * swap still shows the whole batch's state, not just the new delta.
     * Never mutates anything and never computes a box position — an
     * already-picked card was physically pulled in an earlier run, so it
     * isn't sitting in its lot's box any more and has no position to give.
     *
     * @return Collection<int, array{lot: string, cards: Collection}>
     */
    public function alreadyPicked(Batch $batch): Collection
    {
        $targets = CardInventory::whereIn('pack_id', $batch->packs()->pluck('id'))
            ->whereNotNull('picked_at')
            ->with('pack')
            ->get();

        if ($targets->isEmpty()) {
            return collect();
        }

        return $targets
            ->groupBy(fn (CardInventory $card) => $card->acquisition_lot ?? '(no lot recorded)')
            ->sortKeys()
            ->map(fn (Collection $lotTargets, string $lot) => [
                'lot' => $lot,
                'cards' => $lotTargets
                    ->sortBy(fn (CardInventory $card) => $card->chaosSortKey())
                    ->values()
                    ->map(fn (CardInventory $card) => [
                        'card_inventory_id' => $card->id,
                        'card_name' => $card->card_name,
                        'set_name' => $card->set_name,
                        'card_number' => $card->card_number,
                        'rarity' => $card->rarity_band ? ucfirst($card->rarity_band) : 'Unbanded',
                        'product_badges' => $card->product_badges,
                        'pack_sequence' => $card->pack?->sequence_no,
                        'picked_at' => $card->picked_at?->format('d M Y H:i'),
                    ]),
            ])
            ->values();
    }

    /**
     * Every card in this batch (picked or not) currently flagged "on eBay" or
     * "in card wall" — a final can't-miss checklist appended to the end of
     * the picking sheet, since either flag means the card may not actually
     * be sitting in its lot's box where chaos storage assumes it is.
     *
     * @return Collection<int, array{card_inventory_id: int, card_name: string, set_name: ?string, card_number: ?string, pack_sequence: ?int, on_ebay: bool, in_card_wall: bool, product_badges: array}>
     */
    public function specialHandling(Batch $batch): Collection
    {
        return CardInventory::whereIn('pack_id', $batch->packs()->pluck('id'))
            ->where(fn ($q) => $q->where('on_ebay', true)->orWhere('in_card_wall', true))
            ->with('pack')
            ->get()
            ->sortBy(fn (CardInventory $card) => $card->chaosSortKey())
            ->values()
            ->map(fn (CardInventory $card) => [
                'card_inventory_id' => $card->id,
                'card_name' => $card->card_name,
                'set_name' => $card->set_name,
                'card_number' => $card->card_number,
                'pack_sequence' => $card->pack?->sequence_no,
                'on_ebay' => $card->on_ebay,
                'in_card_wall' => $card->in_card_wall,
                'product_badges' => $card->product_badges,
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
