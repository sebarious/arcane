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
        return Inertia::render('Affiliate/Signup', [
            'suggestedCode' => Affiliate::generateAffiliateCode(),
        ]);
    }

    /**
     * Lets the signup form's "Regenerate" button fetch a fresh suggestion
     * without a full page reload — same generator, checked against the same
     * uniqueness constraint, just called on demand instead of once at page load.
     */
    public function suggestCode()
    {
        return response()->json(['code' => Affiliate::generateAffiliateCode()]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'confirmed', 'min:8'],
            // Whatever the user last landed on via the regenerate button —
            // re-checked here since another signup could in theory have
            // claimed it in the meantime.
            'affiliate_code' => ['required', 'string', 'max:30', 'unique:affiliates,affiliate_code'],
        ]);

        [$user, $affiliate] = DB::transaction(function () use ($data) {
            $user = User::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => Hash::make($data['password']),
            ]);

            $user->assignRole('affiliate');

            $affiliate = Affiliate::create([
                'user_id' => $user->id,
                'affiliate_code' => strtoupper($data['affiliate_code']),
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
