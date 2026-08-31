<?php

namespace App\Http\Controllers\Affiliate;

use App\Http\Controllers\Controller;
use App\Models\AffiliateCreditTransaction;
use App\Services\Affiliates\AffiliateCreditService;
use Illuminate\Http\Request;
use Inertia\Inertia;

/**
 * The one route reachable regardless of approval status (see
 * EnsureAffiliateIsApproved's docblock) — renders whichever state applies
 * (pending / active / suspended) rather than redirecting elsewhere.
 */
class DashboardController extends Controller
{
    public function __invoke(Request $request)
    {
        $affiliate = $request->user()->affiliate;

        $transactions = $affiliate && $affiliate->status === 'active'
            ? AffiliateCreditTransaction::query()
                ->where('affiliate_id', $affiliate->id)
                ->with(['customerSellSubmission:id,reference', 'withdrawal:id,status'])
                ->orderByDesc('created_at')
                ->paginate(20)
                ->through(fn (AffiliateCreditTransaction $tx) => [
                    'id' => $tx->id,
                    'created_at' => $tx->created_at->toIso8601String(),
                    'type' => $tx->type,
                    'amount_pence' => $tx->amount_pence,
                    'balance_after_pence' => $tx->balance_after_pence,
                    'reason' => $tx->reason,
                    'submission_reference' => $tx->customerSellSubmission?->reference,
                ])
            : null;

        return Inertia::render('Affiliate/Dashboard', [
            'status' => $affiliate?->status,
            'affiliateCode' => $affiliate?->affiliate_code,
            'balancePence' => $affiliate?->credit_balance_pence ?? 0,
            'minimumWithdrawalBalancePence' => AffiliateCreditService::MINIMUM_WITHDRAWAL_BALANCE_PENCE,
            'transactions' => $transactions,
        ]);
    }
}
