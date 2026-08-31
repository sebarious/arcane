<?php

namespace App\Services\Affiliates;

use App\Models\Affiliate;
use App\Models\AffiliateCreditTransaction;
use App\Models\AffiliateWithdrawal;
use App\Models\CustomerSellSubmission;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class AffiliateCreditService
{
    // Below this balance, requestWithdrawal() refuses outright — matches the
    // "£100 in their wallet" eligibility gate on the dashboard/withdrawals page.
    public const MINIMUM_WITHDRAWAL_BALANCE_PENCE = 10000;

    /**
     * Add credit to an affiliate's wallet — e.g. after appraising cards submitted
     * via their affiliate code. Locks the affiliate row so a concurrent credit and
     * withdrawal request can't race. Mirrors StoreCreditService::addCredit()
     * exactly — same shape, independent ledger.
     */
    public function addCredit(
        Affiliate $affiliate,
        int $amountPence,
        string $reason,
        ?CustomerSellSubmission $submission = null,
        ?User $addedBy = null,
    ): AffiliateCreditTransaction {
        if ($amountPence <= 0) {
            throw new \InvalidArgumentException('Credit amount must be greater than zero.');
        }

        return DB::transaction(function () use ($affiliate, $amountPence, $reason, $submission, $addedBy) {
            $locked = Affiliate::query()->whereKey($affiliate->id)->lockForUpdate()->firstOrFail();

            $newBalance = $locked->credit_balance_pence + $amountPence;
            $locked->update(['credit_balance_pence' => $newBalance]);

            return AffiliateCreditTransaction::create([
                'affiliate_id' => $affiliate->id,
                'type' => 'credit',
                'amount_pence' => $amountPence,
                'balance_after_pence' => $newBalance,
                'reason' => $reason,
                'customer_sell_submission_id' => $submission?->id,
                'created_by_user_id' => $addedBy?->id,
            ]);
        });
    }

    /**
     * Deducts $amountPence immediately (so the balance reflects the pending
     * request right away, same principle as a store's credit being applied to
     * an invoice the moment it's raised) and opens an AffiliateWithdrawal for
     * admin to action. The £100 eligibility gate is checked against the
     * balance *before* the request, not the requested amount — an affiliate
     * with £150 can request £50 even though £50 alone wouldn't have cleared
     * the floor.
     *
     * @throws \RuntimeException if the balance doesn't clear the minimum, the
     *                           requested amount exceeds it, or bank details
     *                           haven't been set yet.
     */
    public function requestWithdrawal(Affiliate $affiliate, int $amountPence): AffiliateWithdrawal
    {
        if ($amountPence <= 0) {
            throw new \InvalidArgumentException('Withdrawal amount must be greater than zero.');
        }

        if (! $affiliate->hasBankDetails()) {
            throw new \RuntimeException('Add your bank details before requesting a withdrawal.');
        }

        return DB::transaction(function () use ($affiliate, $amountPence) {
            $locked = Affiliate::query()->whereKey($affiliate->id)->lockForUpdate()->firstOrFail();

            if ($locked->credit_balance_pence < self::MINIMUM_WITHDRAWAL_BALANCE_PENCE) {
                throw new \RuntimeException('You need more than £100 in your wallet to request a withdrawal.');
            }

            if ($amountPence > $locked->credit_balance_pence) {
                throw new \RuntimeException('You can\'t withdraw more than your current balance.');
            }

            $newBalance = $locked->credit_balance_pence - $amountPence;
            $locked->update(['credit_balance_pence' => $newBalance]);

            $withdrawal = AffiliateWithdrawal::create([
                'affiliate_id' => $affiliate->id,
                'amount_pence' => $amountPence,
                'status' => 'pending',
                'bank_account_name' => $locked->bank_account_name,
                'bank_sort_code' => $locked->bank_sort_code,
                'bank_account_number' => $locked->bank_account_number,
            ]);

            AffiliateCreditTransaction::create([
                'affiliate_id' => $affiliate->id,
                'type' => 'redemption',
                'amount_pence' => -$amountPence,
                'balance_after_pence' => $newBalance,
                'reason' => "Withdrawal requested (#{$withdrawal->id})",
                'affiliate_withdrawal_id' => $withdrawal->id,
            ]);

            return $withdrawal;
        });
    }

    public function markWithdrawalPaid(AffiliateWithdrawal $withdrawal, User $admin): void
    {
        if ($withdrawal->status !== 'pending') {
            throw new \RuntimeException('This withdrawal has already been resolved.');
        }

        $withdrawal->update([
            'status' => 'paid',
            'paid_at' => now(),
            'paid_by_user_id' => $admin->id,
        ]);
    }

    /**
     * The money never actually left — refund it back to the affiliate's wallet
     * with its own credit entry, rather than silently leaving the earlier
     * redemption as the only record.
     */
    public function rejectWithdrawal(AffiliateWithdrawal $withdrawal, User $admin, string $reason): void
    {
        if ($withdrawal->status !== 'pending') {
            throw new \RuntimeException('This withdrawal has already been resolved.');
        }

        DB::transaction(function () use ($withdrawal, $admin, $reason) {
            $withdrawal->update([
                'status' => 'rejected',
                'admin_notes' => $reason,
            ]);

            $this->addCredit(
                $withdrawal->affiliate,
                $withdrawal->amount_pence,
                "Withdrawal request rejected — refunded (#{$withdrawal->id}): {$reason}",
                addedBy: $admin,
            );
        });
    }
}
