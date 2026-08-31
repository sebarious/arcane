<?php

namespace App\Services\Rips;

use App\Models\Rip;
use App\Models\RipWallet;
use App\Models\RipWalletTopup;
use App\Models\RipWalletTransaction;
use App\Models\RipWithdrawal;
use App\Models\User;
use Illuminate\Support\Facades\DB;

/**
 * Digital Rips' wallet — same locked-row-plus-ledger shape as
 * StoreCreditService, independently instantiated for rip customers (see the
 * plan's branch note: no shared code with the affiliate-programme branch's
 * near-identical AffiliateCreditService, just the same design).
 */
class RipWalletService
{
    // £50, vs £100 for affiliates — per the product ask.
    public const MINIMUM_WITHDRAWAL_BALANCE_PENCE = 5000;

    public function addCredit(
        RipWallet $wallet,
        int $amountPence,
        string $reason,
        ?Rip $rip = null,
        ?User $addedBy = null,
        ?RipWalletTopup $topup = null,
    ): RipWalletTransaction {
        if ($amountPence <= 0) {
            throw new \InvalidArgumentException('Credit amount must be greater than zero.');
        }

        return DB::transaction(function () use ($wallet, $amountPence, $reason, $rip, $addedBy, $topup) {
            $locked = RipWallet::query()->whereKey($wallet->id)->lockForUpdate()->firstOrFail();

            $newBalance = $locked->credit_balance_pence + $amountPence;
            $locked->update(['credit_balance_pence' => $newBalance]);

            return RipWalletTransaction::create([
                'rip_wallet_id' => $wallet->id,
                'type' => 'credit',
                'amount_pence' => $amountPence,
                'balance_after_pence' => $newBalance,
                'reason' => $reason,
                'rip_id' => $rip?->id,
                'rip_wallet_topup_id' => $topup?->id,
                'created_by_user_id' => $addedBy?->id,
            ]);
        });
    }

    /**
     * Spends wallet balance on a pack purchase (RipCheckoutService::payFromWallet())
     * — same locked-row-plus-ledger shape as requestWithdrawal(), minus the
     * RipWithdrawal row since nothing leaves the business, it just moves from
     * wallet balance to a Rip purchase.
     *
     * @throws \RuntimeException if the balance doesn't cover the amount.
     */
    public function debitForPurchase(RipWallet $wallet, int $amountPence, string $reason): RipWalletTransaction
    {
        if ($amountPence <= 0) {
            throw new \InvalidArgumentException('Debit amount must be greater than zero.');
        }

        return DB::transaction(function () use ($wallet, $amountPence, $reason) {
            $locked = RipWallet::query()->whereKey($wallet->id)->lockForUpdate()->firstOrFail();

            if ($amountPence > $locked->credit_balance_pence) {
                throw new \RuntimeException('Your wallet balance isn\'t enough to cover this.');
            }

            $newBalance = $locked->credit_balance_pence - $amountPence;
            $locked->update(['credit_balance_pence' => $newBalance]);

            return RipWalletTransaction::create([
                'rip_wallet_id' => $wallet->id,
                'type' => 'redemption',
                'amount_pence' => -$amountPence,
                'balance_after_pence' => $newBalance,
                'reason' => $reason,
            ]);
        });
    }

    /**
     * @return float The fraction charged on top of a withdrawal — 0.03 = 3%.
     *               See requestWithdrawal(); exposed so the frontend can
     *               show the same figure before the customer commits.
     */
    public function withdrawalFeeRate(): float
    {
        return (float) config('rips.withdrawal_fee_rate');
    }

    /**
     * $amountPence is what the customer actually receives — a 3% fee is
     * charged on top and debited from the wallet alongside it, so a £100
     * withdrawal takes £103 off the balance. Snapshotted onto the
     * RipWithdrawal row at request time (see the migration's own note) so a
     * later rate change never rewrites a historical withdrawal.
     *
     * @throws \RuntimeException if the balance doesn't clear the minimum, the
     *                           total (payout + fee) exceeds it, or bank
     *                           details haven't been set yet.
     */
    public function requestWithdrawal(RipWallet $wallet, int $amountPence): RipWithdrawal
    {
        if ($amountPence <= 0) {
            throw new \InvalidArgumentException('Withdrawal amount must be greater than zero.');
        }

        if (! $wallet->hasBankDetails()) {
            throw new \RuntimeException('Add your bank details before requesting a withdrawal.');
        }

        $feePence = (int) round($amountPence * $this->withdrawalFeeRate());
        $totalDebitPence = $amountPence + $feePence;

        return DB::transaction(function () use ($wallet, $amountPence, $feePence, $totalDebitPence) {
            $locked = RipWallet::query()->whereKey($wallet->id)->lockForUpdate()->firstOrFail();

            if ($locked->credit_balance_pence < self::MINIMUM_WITHDRAWAL_BALANCE_PENCE) {
                throw new \RuntimeException('You need more than £50 in your wallet to request a withdrawal.');
            }

            if ($totalDebitPence > $locked->credit_balance_pence) {
                throw new \RuntimeException('You can\'t withdraw more than your current balance, once the 3% fee is included.');
            }

            $newBalance = $locked->credit_balance_pence - $totalDebitPence;
            $locked->update(['credit_balance_pence' => $newBalance]);

            $withdrawal = RipWithdrawal::create([
                'rip_wallet_id' => $wallet->id,
                'amount_pence' => $amountPence,
                'fee_pence' => $feePence,
                'status' => 'pending',
                'bank_account_name' => $locked->bank_account_name,
                'bank_sort_code' => $locked->bank_sort_code,
                'bank_account_number' => $locked->bank_account_number,
            ]);

            RipWalletTransaction::create([
                'rip_wallet_id' => $wallet->id,
                'type' => 'redemption',
                'amount_pence' => -$totalDebitPence,
                'balance_after_pence' => $newBalance,
                'reason' => sprintf(
                    'Withdrawal requested (#%d) — £%s payout + £%s fee (%s%%)',
                    $withdrawal->id,
                    number_format($amountPence / 100, 2),
                    number_format($feePence / 100, 2),
                    rtrim(rtrim(number_format($this->withdrawalFeeRate() * 100, 2), '0'), '.'),
                ),
                'rip_withdrawal_id' => $withdrawal->id,
            ]);

            return $withdrawal;
        });
    }

    public function markWithdrawalPaid(RipWithdrawal $withdrawal, User $admin): void
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
     * The money never actually left — refund it back to the wallet with its
     * own credit entry, including the fee: a rejected withdrawal must fully
     * reverse, not leave the 3% charge behind as if it had gone through.
     */
    public function rejectWithdrawal(RipWithdrawal $withdrawal, User $admin, string $reason): void
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
                $withdrawal->wallet,
                $withdrawal->amount_pence + $withdrawal->fee_pence,
                "Withdrawal request rejected — refunded (#{$withdrawal->id}): {$reason}",
                addedBy: $admin,
            );
        });
    }
}
