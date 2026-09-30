<?php

namespace App\Http\Controllers\Rips;

use App\Http\Controllers\Controller;
use App\Models\RipWallet;
use App\Models\RipWithdrawal;
use App\Services\Rips\RipWalletService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;

class WithdrawalController extends Controller
{
    /** GET /rips/wallet/withdrawals — request form + this customer's withdrawal history. */
    public function show(Request $request, RipWalletService $walletService)
    {
        $wallet = $request->user()->ripWallet ?? RipWallet::create(['user_id' => $request->user()->id]);

        return Inertia::render('Rips/Withdrawals', [
            'balance_pence' => $wallet->credit_balance_pence,
            'has_bank_details' => $wallet->hasBankDetails(),
            'minimum_withdrawal_pence' => RipWalletService::MINIMUM_WITHDRAWAL_BALANCE_PENCE,
            'withdrawal_fee_rate' => $walletService->withdrawalFeeRate(),
            'bank_account_name' => $wallet->bank_account_name,
            'bank_sort_code' => $wallet->bank_sort_code,
            'bank_account_number' => $wallet->bank_account_number,
            'withdrawals' => RipWithdrawal::where('rip_wallet_id', $wallet->id)
                ->orderByDesc('created_at')
                ->get()
                ->map(fn (RipWithdrawal $w) => [
                    'id' => $w->id,
                    'amount_pence' => $w->amount_pence,
                    'fee_pence' => $w->fee_pence,
                    'status' => $w->status,
                    'created_at' => $w->created_at->toIso8601String(),
                    'paid_at' => $w->paid_at?->toIso8601String(),
                ]),
        ]);
    }

    /** POST /rips/wallet/withdrawals */
    public function store(Request $request, RipWalletService $walletService): JsonResponse
    {
        $wallet = $request->user()->ripWallet ?? RipWallet::create(['user_id' => $request->user()->id]);

        $data = $request->validate([
            'amount_pence' => ['required', 'integer', 'min:1'],
        ]);

        try {
            $withdrawal = $walletService->requestWithdrawal($wallet, $data['amount_pence']);
        } catch (\RuntimeException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }

        return response()->json([
            'data' => [
                'id' => $withdrawal->id,
                'amount_pence' => $withdrawal->amount_pence,
                'fee_pence' => $withdrawal->fee_pence,
                'status' => $withdrawal->status,
            ],
        ]);
    }
}
