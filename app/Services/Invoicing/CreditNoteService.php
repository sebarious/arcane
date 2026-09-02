<?php

namespace App\Services\Invoicing;

use App\Models\CreditNote;
use App\Models\Invoice;
use App\Models\Store;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CreditNoteService
{
    /**
     * Issues a new credit note against a store — the refund exists from this
     * point on but isn't settled yet; an admin resolves it afterwards via
     * exactly one of applyToInvoice() or markPaid(). The "sent to the store
     * owner once generated" email is dispatched by the caller (the Filament
     * create page), not here, so this stays a plain, easily-tested write.
     */
    public function create(Store $store, int $amountPence, string $reason, ?User $issuedBy = null): CreditNote
    {
        if ($amountPence <= 0) {
            throw ValidationException::withMessages(['amount' => 'Credit note amount must be greater than zero.']);
        }

        return CreditNote::create([
            'number' => CreditNote::nextNumber(),
            'store_id' => $store->id,
            'amount_pence' => $amountPence,
            'reason' => $reason,
            'status' => 'issued',
            'issued_by_user_id' => $issuedBy?->id,
        ]);
    }

    /**
     * Resolves a credit note by reducing what the store still owes on a
     * specific invoice. Mirrors StoreCreditService::deductForInvoice() in
     * spirit but is additive rather than a set — an invoice can already carry
     * store-credit applied at generation time, and this must stack with that,
     * not overwrite it. total_pence is never touched; amount_due_pence
     * (total_pence - credit_applied_pence) is what reflects the reduction.
     */
    public function applyToInvoice(CreditNote $creditNote, Invoice $invoice): CreditNote
    {
        if ($creditNote->status !== 'issued') {
            throw ValidationException::withMessages(['status' => 'This credit note has already been resolved.']);
        }

        if ($invoice->store_id !== $creditNote->store_id) {
            throw ValidationException::withMessages(['invoice_id' => 'That invoice does not belong to this store.']);
        }

        return DB::transaction(function () use ($creditNote, $invoice) {
            $lockedInvoice = Invoice::whereKey($invoice->id)->lockForUpdate()->first();

            if ($creditNote->amount_pence > $lockedInvoice->amount_due_pence) {
                throw ValidationException::withMessages([
                    'invoice_id' => 'The credit note amount is more than this invoice\'s outstanding balance.',
                ]);
            }

            $lockedInvoice->increment('credit_applied_pence', $creditNote->amount_pence);

            $creditNote->update([
                'invoice_id' => $lockedInvoice->id,
                'status' => 'applied',
                'applied_at' => now(),
            ]);

            return $creditNote->fresh();
        });
    }

    /**
     * Resolves a credit note by recording that the store was paid back
     * directly (e.g. bank transfer) outside the system — no invoice or wallet
     * balance is touched, this is purely the bookkeeping record.
     */
    public function markPaid(CreditNote $creditNote, User $paidBy): CreditNote
    {
        if ($creditNote->status !== 'issued') {
            throw ValidationException::withMessages(['status' => 'This credit note has already been resolved.']);
        }

        $creditNote->update([
            'status' => 'paid',
            'paid_at' => now(),
            'paid_by_user_id' => $paidBy->id,
        ]);

        return $creditNote->fresh();
    }
}
