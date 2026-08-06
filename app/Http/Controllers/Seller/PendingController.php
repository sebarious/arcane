<?php

namespace App\Http\Controllers\Seller;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;

class PendingController extends Controller
{
    public function __invoke(Request $request)
    {
        return Inertia::render('Seller/Pending', [
            'affiliateCode' => $request->user()->store?->affiliate_code,
            'bonusPercentage' => (float) config('selling.affiliate_bonus_percentage', 0.05),
        ]);
    }
}
