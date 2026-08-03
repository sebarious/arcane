<?php

namespace App\Services\Stores;

use App\Models\CustomerSellSubmission;
use App\Models\Invoice;
use App\Models\Store;
use App\Models\StoreCreditTransaction;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class StoreCreditService
{
    /**
     * Add credit to a store's wallet — e.g. after appraising cards submitted via
     * their affiliate code. Locks the store row so concurrent credits/redemptions
     * (an admin crediting the store while a batch is generating, say) can't race.
     */
    public function addCredit(
        Store $store,
        int $amountPence,
        string $reason,
        ?CustomerSellSubmission $submission = null,
        ?User $addedBy = null,
    ): StoreCreditTransaction {
        if ($amountPence <= 0) {
            throw new \InvalidArgumentException('Credit amount must be greater than zero.');
        }

        return DB::transaction(function () use ($store, $amountPence, $reason, $submission, $addedBy) {
            $locked = Store::query()->whereKey($store->id)->lockForUpdate()->firstOrFail();

            $newBalance = $locked->credit_balance_pence + $amountPence;
            $locked->update(['credit_balance_pence' => $newBalance]);

            return StoreCreditTransaction::create([
                'store_id'                    => $store->id,
                'type'                        => 'credit',
                'amount_pence'                => $amountPence,
                'balance_after_pence'         => $newBalance,
                'reason'                      => $reason,
                'customer_sell_submission_id' => $submission?->id,
                'created_by_user_id'          => $addedBy?->id,
            ]);
        });
    }

    /**
     * Apply as much of a store's available credit as possible against a freshly
     * generated invoice — called once, right when the invoice is created. Never
     * applies more than the invoice is worth; any leftover credit carries forward.
     * A no-op (no ledger entry) if the store has no credit to apply.
     */
    public function deductForInvoice(Store $store, Invoice $invoice): int
    {
        return DB::transaction(function () use ($store, $invoice) {
            $locked = Store::query()->whereKey($store->id)->lockForUpdate()->firstOrFail();

            $toApply = min($locked->credit_balance_pence, $invoice->total_pence);
            if ($toApply <= 0) {
                return 0;
            }

            $newBalance = $locked->credit_balance_pence - $toApply;
            $locked->update(['credit_balance_pence' => $newBalance]);

            StoreCreditTransaction::create([
                'store_id'             => $store->id,
                'type'                 => 'redemption',
                'amount_pence'         => -$toApply,
                'balance_after_pence'  => $newBalance,
                'reason'               => "Applied to invoice {$invoice->number}",
                'invoice_id'           => $invoice->id,
            ]);

            $invoice->update(['credit_applied_pence' => $toApply]);

            return $toApply;
        });
    }
}
