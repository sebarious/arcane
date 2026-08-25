<?php

namespace App\Services\Batches;

use App\Models\Batch;
use App\Models\CardInventory;
use App\Models\Pack;
use App\Models\User;
use App\Support\Money;
use Illuminate\Support\Facades\DB;

/**
 * A bulk sibling to CardSwapper: replaces every not-yet-sold pack's card in
 * one go, each with a fresh same-rarity-band pick, rather than one at a time.
 * Same eligibility rules and "never touches BatchVerifier/the verification
 * snapshot" guarantee as CardSwapper — this is an admin corrective action
 * outside the provably-fair envelope, not a redo of the original seeded
 * draw, so replacements are plain random rather than SeededRandom.
 *
 * Deliberately blocks once any of this batch's cards have picked_at set —
 * unlike a single swap (an admin already knows what they're doing for one
 * card), rerolling potentially the entire batch after warehouse picking has
 * started would leave staff holding a pile of now-wrong physical cards.
 */
class BatchReroller
{
    protected const ELIGIBLE_BATCH_STATUSES = ['pending_review', 'awaiting_payment', 'committed'];

    /**
     * @return int Number of packs re-rolled.
     *
     * @throws \RuntimeException if the batch isn't eligible, has nothing left to
     *                           re-roll, picking has already started, or stock can't
     *                           cover it.
     */
    public function reroll(Batch $batch, string $reason, ?int $byUserId): int
    {
        if (! in_array($batch->status, self::ELIGIBLE_BATCH_STATUSES, true)) {
            throw new \RuntimeException("Batch {$batch->reference} isn't in a status where it can be re-rolled ({$batch->status}).");
        }

        return DB::transaction(function () use ($batch, $reason, $byUserId) {
            $packs = $batch->packs()
                ->where('status', 'sealed')
                ->with('card')
                ->get()
                ->filter(fn (Pack $pack) => $pack->card !== null)
                ->values();

            if ($packs->isEmpty()) {
                throw new \RuntimeException("Batch {$batch->reference} has no sealed packs left to re-roll.");
            }

            if (CardInventory::whereIn('pack_id', $packs->pluck('id'))->whereNotNull('picked_at')->exists()) {
                throw new \RuntimeException('Some of this batch\'s cards have already been picked for the warehouse — use "Swap a card" for individual corrections instead.');
            }

            $duplicateLimits = config('banding.duplicate_limits', []);
            $replacements = collect(); // pack_id => CardInventory

            foreach ($packs->groupBy(fn (Pack $pack) => $pack->card->rarity_band) as $band => $bandPacks) {
                $limitPerCard = (int) ($duplicateLimits[$band] ?? 1);
                $usedPerProduct = [];

                $candidates = CardInventory::available()
                    ->where('game', $batch->game->value)
                    ->where('rarity_band', $band)
                    ->inRandomOrder()
                    ->get();

                foreach ($bandPacks as $pack) {
                    $index = $candidates->search(
                        fn (CardInventory $candidate) => ($usedPerProduct[$candidate->product_id] ?? 0) < $limitPerCard
                    );

                    if ($index === false) {
                        throw new \RuntimeException("Not enough distinct {$band} stock available to re-roll all {$bandPacks->count()} pack(s) in that band.");
                    }

                    $replacement = $candidates->pull($index);
                    $usedPerProduct[$replacement->product_id] = ($usedPerProduct[$replacement->product_id] ?? 0) + 1;
                    $replacements->put($pack->id, $replacement);
                }
            }

            $allocatedSalePrice = (int) floor($batch->sale_price_pence / $batch->pack_count);

            foreach ($packs as $pack) {
                $pack->card->update([
                    'pack_id' => null,
                    'status' => 'in_stock',
                    'qr_token' => null,
                    'allocated_sale_price_pence' => null,
                    'margin_pence' => null,
                    'picked_at' => null,
                ]);

                $replacements[$pack->id]->update([
                    'pack_id' => $pack->id,
                    'status' => 'allocated',
                    'qr_token' => CardInventory::generateQrToken(),
                    'allocated_sale_price_pence' => $allocatedSalePrice,
                    'margin_pence' => null,
                ]);
            }

            // Recompute batch-level totals across every card still tied to this
            // batch — including already-sold packs' cards (their cost/margin is
            // frozen history), not just the ones just replaced.
            $currentCards = CardInventory::whereIn('pack_id', $batch->packs()->pluck('id'))->get();

            $totalCost = (int) $currentCards->sum('cost_pence');
            $totalMarket = (int) $currentCards->sum('market_value_pence');
            $margin = $batch->sale_price_pence - $totalCost;
            $perCardMargin = (int) floor($margin / max(1, $currentCards->count()));

            CardInventory::whereIn('id', $currentCards->pluck('id'))->update(['margin_pence' => $perCardMargin]);

            $topCards = $currentCards->sortByDesc('market_value_pence')->values();

            $who = $byUserId ? (User::find($byUserId)?->name ?? "user #{$byUserId}") : 'system';
            $note = sprintf(
                '[%s] Complete re-roll by %s: %d card(s) replaced. Reason: %s',
                now()->format('d M Y H:i'),
                $who,
                $replacements->count(),
                $reason,
            );

            $batch->update([
                'total_cost_pence' => $totalCost,
                'total_market_value_pence' => $totalMarket,
                'margin_pence' => $margin,
                'margin_scheme_vat_pence' => Money::marginSchemeVat($margin),
                'top_card_1_id' => $topCards->get(0)?->id,
                'top_card_2_id' => $topCards->get(1)?->id,
                'admin_notes' => trim(($batch->admin_notes ? $batch->admin_notes."\n" : '').$note),
            ]);

            return $replacements->count();
        });
    }
}
