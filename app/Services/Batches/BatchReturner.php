<?php

namespace App\Services\Batches;

use App\Models\Batch;
use App\Models\User;
use App\Services\Invoicing\CreditNoteService;
use App\Services\Stores\StoreCreditService;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * A shipped batch the store sent back (unopened, nothing sold) — voids its
 * invoice and returns the batch itself to the unassigned premade pool
 * (store_id null, status pending_review) so it can be picked up by
 * PremadeBatchAssigner and handed to a store again. The packs/cards
 * themselves are untouched: nothing was ever sold, so the exact same sealed
 * packs are what go back out the door next time.
 */
class BatchReturner
{
    public function __construct(
        protected StoreCreditService $creditService,
        protected CreditNoteService $creditNoteService,
    ) {}

    /**
     * @throws \RuntimeException if the batch isn't a clean, unsold return.
     */
    public function returnBatch(Batch $batch, ?string $reason, ?User $performedBy = null): void
    {
        if ($batch->status !== 'dispatched') {
            throw new \RuntimeException("Batch {$batch->reference} isn't marked as shipped — only a dispatched batch can be returned.");
        }

        if ($batch->packs()->where('status', 'sold')->exists()) {
            throw new \RuntimeException("Batch {$batch->reference} already has pack(s) sold — this isn't a clean return. Use a card swap/re-roll for individual cards instead.");
        }

        DB::transaction(function () use ($batch, $reason, $performedBy) {
            $store = $batch->store;
            $storeName = $store?->name ?? 'no store';
            $invoice = $batch->invoice;
            $noteReason = "Batch {$batch->reference} returned".($reason ? " — {$reason}" : '').'.';

            if ($invoice && $invoice->status !== 'cancelled') {
                // Give back whatever wallet credit was auto-applied to this
                // invoice at generation time (see StoreCreditService::
                // deductForInvoice) — a separate pot from the credit note
                // below, so this doesn't touch the invoice's own numbers.
                $this->creditService->reverseInvoiceCredit($invoice, $noteReason, $performedBy);

                // Whatever's still actually outstanding on the invoice (paid
                // directly, or never covered by wallet credit) gets voided
                // via a proper credit note tied to this invoice — the same
                // document trail an admin would use for any other refund,
                // rather than a bespoke status flag.
                if ($invoice->amount_due_pence > 0) {
                    try {
                        $creditNote = $this->creditNoteService->create($store, $invoice->amount_due_pence, $noteReason, $performedBy);
                        $this->creditNoteService->applyToInvoice($creditNote, $invoice);
                    } catch (ValidationException $e) {
                        throw new \RuntimeException(collect($e->errors())->flatten()->join(' '));
                    }
                }

                $invoice->update(['status' => 'cancelled']);
            }

            $batch->update([
                'store_id' => null,
                'status' => 'pending_review',
                'committed_at' => null,
                'dispatched_at' => null,
                'tracking_number' => null,
                'tracking_url' => null,
                'admin_notes' => trim(collect([
                    $batch->admin_notes,
                    'Returned from '.$storeName.' on '.now()->format('d M Y').
                        ($reason ? " — {$reason}" : '').
                        ' — back in the unassigned pool, ready to be assigned to a store again.',
                ])->filter()->implode("\n\n")),
            ]);
        });
    }
}
