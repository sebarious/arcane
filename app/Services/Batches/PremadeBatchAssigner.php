<?php

namespace App\Services\Batches;

use App\Models\Batch;
use App\Models\Pack;
use Illuminate\Support\Facades\DB;

/**
 * Assigns "premade" batches — generated ahead of time with no store_id, so
 * warehouse staff can get ahead of packing before a specific order exists —
 * to a store.
 */
class PremadeBatchAssigner
{
    /**
     * Directly hand a ready premade batch to a store — no request record
     * involved, just fills in store_id so it enters the normal
     * invoice -> publish flow from here.
     */
    public function assignToStore(Batch $premade, int $storeId): void
    {
        $this->assertIsReadyPremade($premade);

        $premade->update(['store_id' => $storeId]);
    }

    /**
     * Fulfils a store-bound request that has no cards yet (a fresh manual
     * batch, or one created by BatchRequestController) using an already-
     * generated premade batch instead of running BatchGenerator::generate()
     * for it.
     *
     * The *request's* row is what survives — its reference, URL, and
     * verification commitment are the ones a seller may already have seen
     * (BatchRequestController flashes the reference immediately on
     * submission), so packs move onto it rather than the other way around.
     * Nothing about the premade batch's own generation was ever shown to
     * anyone before this point, so copying its verification fields across is
     * not a fairness violation — it's just relabelling which row that
     * already-committed draw belongs to. The now-empty premade batch is then
     * deleted.
     */
    public function fulfillRequest(Batch $request, Batch $premade): void
    {
        if ($request->status !== 'draft' || $request->packs()->exists()) {
            throw new \RuntimeException("Batch {$request->reference} already has cards — it can't be replaced by a premade batch.");
        }

        if (! $request->store_id) {
            throw new \RuntimeException("Batch {$request->reference} has no store assigned to fulfil.");
        }

        $this->assertIsReadyPremade($premade);

        if ($premade->game !== $request->game || $premade->type !== $request->type) {
            throw new \RuntimeException('The premade batch must match the requested game and product type.');
        }

        DB::transaction(function () use ($request, $premade) {
            Pack::where('batch_id', $premade->id)->update(['batch_id' => $request->id]);

            $request->update([
                'status' => $premade->status,
                'total_cost_pence' => $premade->total_cost_pence,
                'total_market_value_pence' => $premade->total_market_value_pence,
                'sale_price_pence' => $premade->sale_price_pence,
                'margin_pence' => $premade->margin_pence,
                'margin_scheme_vat_pence' => $premade->margin_scheme_vat_pence,
                'top_card_1_id' => $premade->top_card_1_id,
                'top_card_2_id' => $premade->top_card_2_id,
                'verification_seed' => $premade->verification_seed,
                'verification_hash' => $premade->verification_hash,
                'verification_committed_at' => $premade->verification_committed_at,
                'verification_revealed_at' => $premade->verification_revealed_at,
                'verification_snapshot_path' => $premade->verification_snapshot_path,
                'admin_notes' => trim(collect([
                    $request->admin_notes,
                    "Fulfilled from premade batch {$premade->reference}.",
                ])->filter()->implode("\n\n")),
            ]);

            $premade->delete();
        });
    }

    private function assertIsReadyPremade(Batch $premade): void
    {
        if ($premade->store_id !== null) {
            throw new \RuntimeException("Batch {$premade->reference} is already assigned to a store.");
        }

        if ($premade->status !== 'pending_review') {
            throw new \RuntimeException("Batch {$premade->reference} isn't a ready premade batch (status: {$premade->status}).");
        }
    }
}
