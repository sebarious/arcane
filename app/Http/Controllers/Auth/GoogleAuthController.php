<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;

class GoogleAuthController extends Controller
{
    public function redirect()
    {
        return Socialite::driver('google')->redirect();
    }

    public function callback()
    {
        $googleUser = Socialite::driver('google')->user();

        // Match an existing account first by google_id, then by email (so
        // someone who already registered with email/password and later
        // clicks "Continue with Google" lands on the same account rather
        // than accidentally creating a second one).
        $user = User::where('google_id', $googleUser->getId())->first()
            ?? User::where('email', $googleUser->getEmail())->first();

        if ($user) {
            if (blank($user->google_id)) {
                $user->update(['google_id' => $googleUser->getId()]);
            }
        } else {
            $user = User::create([
                'name' => $googleUser->getName() ?: $googleUser->getNickname() ?: 'Arcane Customer',
                'email' => $googleUser->getEmail(),
                // Never used to log in — this account only ever authenticates
                // via Google — but the users.password column isn't nullable.
                'password' => Hash::make(Str::random(40)),
                'google_id' => $googleUser->getId(),
            ]);
        }

        Auth::login($user);

        return redirect('/');
    }
}
