<?php

namespace App\Http\Controllers\Rips;

use App\Http\Controllers\Controller;
use App\Models\RipWallet;
use App\Models\RipWalletTransaction;
use App\Services\Rips\RipWalletService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;

class WalletController extends Controller
{
    /** GET /rips/wallet — balance + ledger, mirrors Seller/WalletController's shape. */
    public function show(Request $request)
    {
        $wallet = $this->walletFor($request);

        $transactions = RipWalletTransaction::query()
            ->where('rip_wallet_id', $wallet->id)
            ->with('rip:id,pack_name')
            ->orderByDesc('created_at')
            ->paginate(20)
            ->through(fn (RipWalletTransaction $tx) => [
                'id' => $tx->id,
                'created_at' => $tx->created_at->toIso8601String(),
                'type' => $tx->type,
                'amount_pence' => $tx->amount_pence,
                'balance_after_pence' => $tx->balance_after_pence,
                'reason' => $tx->reason,
                'pack_name' => $tx->rip?->pack_name,
            ]);

        return Inertia::render('Rips/Wallet', [
            'stripeKey' => config('services.stripe.key'),
            'balance_pence' => $wallet->credit_balance_pence,
            'has_bank_details' => $wallet->hasBankDetails(),
            'minimum_withdrawal_pence' => RipWalletService::MINIMUM_WITHDRAWAL_BALANCE_PENCE,
            'transactions' => $transactions,
        ]);
    }

    /** POST /rips/wallet/bank-details — snapshotted onto the wallet, then copied onto each withdrawal at request time. */
    public function updateBankDetails(Request $request): JsonResponse
    {
        $data = $request->validate([
            'bank_account_name' => ['required', 'string', 'max:255'],
            'bank_sort_code' => ['required', 'string', 'max:20'],
            'bank_account_number' => ['required', 'string', 'max:20'],
        ]);

        $this->walletFor($request)->update($data);

        return response()->json(['data' => ['saved' => true]]);
    }

    private function walletFor(Request $request): RipWallet
    {
        return $request->user()->ripWallet ?? RipWallet::create(['user_id' => $request->user()->id]);
    }
}
