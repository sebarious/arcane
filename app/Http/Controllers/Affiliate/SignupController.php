<?php

namespace App\Http\Controllers\Affiliate;

use App\Http\Controllers\Controller;
use App\Mail\AffiliateSignupSubmittedMail;
use App\Models\Affiliate;
use App\Models\User;
use Filament\Notifications\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Inertia\Inertia;

/**
 * The one self-service registration flow in the app — unlike a seller
 * (created by an admin via SellerApplicationApprover with a throwaway
 * password + reset link), an affiliate sets their own real password right
 * here. No approval is needed to create the account or log in; approval only
 * gates the dashboard itself (see EnsureAffiliateIsApproved).
 */
class SignupController extends Controller
{
    public function show()
    {
        return Inertia::render('Affiliate/Signup');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'confirmed', 'min:8'],
        ]);

        // affiliate_code isn't collected here — Affiliate::booted() generates a
        // placeholder on creation, and an admin sets the real one manually when
        // approving the signup (see AffiliateResource's edit form).
        [$user, $affiliate] = DB::transaction(function () use ($data) {
            $user = User::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => Hash::make($data['password']),
            ]);

            $user->assignRole('affiliate');

            $affiliate = Affiliate::create([
                'user_id' => $user->id,
            ]);

            return [$user, $affiliate];
        });

        foreach (User::role('admin')->get() as $admin) {
            Mail::to($admin->email)->send(new AffiliateSignupSubmittedMail($affiliate));

            Notification::make()
                ->title('New affiliate signup')
                ->body("{$user->name} signed up as an affiliate and needs approval.")
                ->sendToDatabase($admin);
        }

        Auth::login($user);

        return redirect()->route('affiliate.dashboard');
    }
}
