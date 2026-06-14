<?php

namespace App\Http\Controllers\Sell;

use App\Http\Controllers\Controller;
use App\Models\CustomerSellSubmission;
use Inertia\Inertia;

class SubmissionThankYouController extends Controller
{
    public function __invoke(string $reference)
    {
        $submission = CustomerSellSubmission::where('reference', $reference)->firstOrFail();

        return Inertia::render('Sell/ThankYou', [
            'reference' => $submission->reference,
        ]);
    }
}
