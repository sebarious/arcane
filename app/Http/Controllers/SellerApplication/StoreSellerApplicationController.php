<?php

namespace App\Http\Controllers\SellerApplication;

use App\Http\Controllers\Controller;
use App\Models\SellerApplication;
use Illuminate\Http\Request;
use App\Mail\SellerApplicationSubmittedMail;
use App\Models\User;
use Illuminate\Support\Facades\Mail;
use App\Mail\SellerApplicationReceivedMail;
use Filament\Notifications\Notification;

class StoreSellerApplicationController extends Controller
{
    public function __invoke(Request $request)
    {
        $data = $request->validate([
            'business_name'  => ['required', 'string', 'max:255'],
            'contact_name'   => ['required', 'string', 'max:255'],
            'contact_email'  => ['required', 'email', 'max:255'],
            'phone'          => ['nullable', 'string', 'max:50'],
            'address_line_1' => ['required', 'string', 'max:255'],
            'address_line_2' => ['nullable', 'string', 'max:255'],
            'city'           => ['required', 'string', 'max:255'],
            'postcode'       => ['required', 'string', 'max:30'],
            'country'        => ['required', 'string', 'size:2'],
            'vat_number'     => ['nullable', 'string', 'max:50'],
            'about'          => ['nullable', 'string', 'max:4000'],
        ]);

        $application = SellerApplication::create([
            ...$data,
            'status'  => 'pending',
            'user_id' => $request->user()?->id,
        ]);
        if ($application->contact_email) {
            Mail::to($application->contact_email)->send(
                new SellerApplicationReceivedMail($application)
            );
        }

        $admins = User::role('admin')->get();
        foreach ($admins as $admin) {
            Mail::to($admin->email)->send(
                new SellerApplicationSubmittedMail($application)
            );

            Notification::make()
                ->title('New seller application submitted')
                ->body("{$application->contact_name} submitted a new seller application.")
                ->sendToDatabase($admin);
        }

        return redirect()->route('application.thankyou');
    }
}
