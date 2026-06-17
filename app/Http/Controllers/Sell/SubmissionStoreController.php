<?php

namespace App\Http\Controllers\Sell;

use App\Http\Controllers\Controller;
use App\Mail\CustomerSellSubmissionAdminAlertMail;
use App\Mail\CustomerSellSubmissionReceivedMail;
use App\Models\CustomerSellSubmission;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\Rules\File;
use Filament\Notifications\Notification;

class SubmissionStoreController extends Controller
{
    public function __invoke(Request $request)
    {
        $validated = $request->validate([
            'customer_name'     => ['required', 'string', 'max:255'],
            'customer_email'    => ['required', 'email', 'max:255'],
            'customer_phone'    => ['nullable', 'string', 'max:50'],
            'customer_postcode' => ['nullable', 'string', 'max:20'],
            'description'       => ['required', 'string', 'max:4000'],
            'images'            => ['required', 'array', 'min:1', 'max:8'],
            'images.*'          => [
                'required',
                File::image()->max(8 * 1024),
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

        // Acknowledge the customer
        if ($submission->customer_email) {
            Mail::to($submission->customer_email)->send(
                new CustomerSellSubmissionReceivedMail($submission)
            );
        }

        // Notify all admins
        $admins = User::role('admin')->get();

        foreach ($admins as $admin) {
            Mail::to($admin->email)->send(
                new CustomerSellSubmissionAdminAlertMail($submission)
            );

            Notification::make()
                ->title('New customer sell submission')
                ->body("{$submission->customer_name} submitted a new sell request.")
                ->sendToDatabase($admin);
        }

        return redirect()
            ->route('sell.thankyou', ['reference' => $submission->reference]);
    }
}
