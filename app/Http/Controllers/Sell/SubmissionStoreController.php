<?php

namespace App\Http\Controllers\Sell;

use App\Http\Controllers\Controller;
use App\Models\CustomerSellSubmission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rules\File;
use Inertia\Inertia;

class SubmissionStoreController extends Controller
{
    public function __invoke(Request $request)
    {
        $validated = $request->validate([
            'customer_name'    => ['required', 'string', 'max:255'],
            'customer_email'   => ['required', 'email', 'max:255'],
            'customer_phone'   => ['nullable', 'string', 'max:50'],
            'customer_postcode' => ['nullable', 'string', 'max:20'],
            'description'      => ['required', 'string', 'max:4000'],
            'images'           => ['required', 'array', 'min:1', 'max:8'],
            'images.*'         => [
                'required',
                File::image()->max(8 * 1024), // 8MB per image
            ],
        ]);

        $paths = [];
        foreach ($validated['images'] as $file) {
            $paths[] = $file->store('sell-submissions', 'public');
        }

        $submission = CustomerSellSubmission::create([
            'reference'         => CustomerSellSubmission::nextReference(),
            'customer_name'     => $validated['customer_name'],
            'customer_email'    => $validated['customer_email'],
            'customer_phone'    => $validated['customer_phone'] ?? null,
            'customer_postcode' => $validated['customer_postcode'] ?? null,
            'description'       => $validated['description'],
            'images'            => $paths,
            'status'            => 'submitted',
        ]);

        return redirect()
            ->route('sell.thankyou', ['reference' => $submission->reference]);
    }
}
