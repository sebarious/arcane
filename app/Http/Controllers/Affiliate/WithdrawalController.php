<?php

namespace App\Http\Controllers\Affiliate;

use App\Http\Controllers\Controller;
use App\Models\AffiliateWithdrawal;
use App\Services\Affiliates\AffiliateCreditService;
use App\Support\Money;
use Illuminate\Http\Request;
use Inertia\Inertia;

class WithdrawalController extends Controller
{
    public function index(Request $request)
    {
        $affiliate = $request->user()->affiliate;

        return Inertia::render('Affiliate/Withdrawals', [
            'balancePence' => $affiliate->credit_balance_pence,
            'minimumWithdrawalBalancePence' => AffiliateCreditService::MINIMUM_WITHDRAWAL_BALANCE_PENCE,
            'hasBankDetails' => $affiliate->hasBankDetails(),
            'withdrawals' => $affiliate->withdrawals()
                ->orderByDesc('created_at')
                ->get()
                ->map(fn (AffiliateWithdrawal $w) => [
                    'id' => $w->id,
                    'amount_pence' => $w->amount_pence,
                    'status' => $w->status,
                    'requested_at' => $w->created_at->format('d M Y'),
                    'paid_at' => $w->paid_at?->format('d M Y'),
                ]),
        ]);
    }

    public function store(Request $request, AffiliateCreditService $service)
    {
        $affiliate = $request->user()->affiliate;

        $data = $request->validate([
            'amount' => ['required', 'numeric', 'min:0.01'],
        ]);

        try {
            $service->requestWithdrawal($affiliate, Money::toPence($data['amount']));
        } catch (\RuntimeException $e) {
            return back()->withErrors(['amount' => $e->getMessage()]);
        }

        return back()->with('success', 'Withdrawal requested — this can take up to 5 working days to process.');
    }
}
