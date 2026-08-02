<?php

namespace App\Http\Controllers\Sell;

use App\Http\Controllers\Controller;
use App\Models\CustomerSellSubmission;
use App\Support\Money;
use Inertia\Inertia;

class SubmissionThankYouController extends Controller
{
    public function __invoke(string $reference)
    {
        $submission = CustomerSellSubmission::where('reference', $reference)
            ->with('items')
            ->firstOrFail();

        return Inertia::render('Sell/ThankYou', [
            'reference'       => $submission->reference,
            'shippingAddress' => config('selling.shipping_address'),
            'estimatedTotal'  => Money::format($submission->estimated_value_pence),
            'items'           => $submission->items->map(fn ($item) => [
                'card_name'         => $item->card_name,
                'set_name'          => $item->set_name,
                'card_number'       => $item->card_number,
                'quantity'          => $item->quantity,
                'total_offer_pence' => $item->total_offer_pence,
                'total_offer'       => Money::format($item->total_offer_pence),
            ]),
        ]);
    }
}
