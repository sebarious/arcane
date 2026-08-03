<?php

namespace App\Http\Controllers\Seller;

use App\Http\Controllers\Controller;
use App\Models\StoreCreditTransaction;
use Illuminate\Http\Request;
use Inertia\Inertia;

class WalletController extends Controller
{
    public function __invoke(Request $request)
    {
        $user = $request->user();
        $stores = $user->stores()->get(['id', 'name', 'credit_balance_pence']);

        $transactions = StoreCreditTransaction::query()
            ->whereIn('store_id', $stores->pluck('id'))
            ->with(['store:id,name', 'invoice:id,number', 'customerSellSubmission:id,reference'])
            ->orderByDesc('created_at')
            ->paginate(20)
            ->through(fn (StoreCreditTransaction $tx) => [
                'id'                  => $tx->id,
                'created_at'          => $tx->created_at->toIso8601String(),
                'type'                => $tx->type,
                'amount_pence'        => $tx->amount_pence,
                'balance_after_pence' => $tx->balance_after_pence,
                'reason'              => $tx->reason,
                'store_name'          => $tx->store?->name,
                'invoice_number'      => $tx->invoice?->number,
                'submission_reference' => $tx->customerSellSubmission?->reference,
            ]);

        return Inertia::render('Seller/Wallet', [
            'stores'       => $stores,
            'totalBalance' => $stores->sum('credit_balance_pence'),
            'transactions' => $transactions,
        ]);
    }
}
