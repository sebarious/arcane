<?php

namespace App\Http\Controllers\Sell;

use App\Http\Controllers\Controller;
use App\Services\Selling\SellOfferCalculator;
use Illuminate\Http\Request;
use Inertia\Inertia;

class SubmissionCreateController extends Controller
{
    public function __invoke(Request $request, SellOfferCalculator $calculator)
    {
        return Inertia::render('Sell/Create', [
            'affiliateCode' => $request->query('affiliate'),
            'buyPercentageBands' => $calculator->bandSummary(),
        ]);
    }
}
