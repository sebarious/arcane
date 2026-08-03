<?php

namespace App\Http\Controllers\Pages;

use App\Http\Controllers\Controller;
use Inertia\Inertia;

class AffiliateProgramController extends Controller
{
    public function __invoke()
    {
        return Inertia::render('Marketing/AffiliateProgram', [
            'bonusPercentage' => (float) config('selling.affiliate_bonus_percentage', 0.05),
        ]);
    }
}
