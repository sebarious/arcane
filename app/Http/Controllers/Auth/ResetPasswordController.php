<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Inertia\Inertia;

class ResetPasswordController extends Controller
{
    public function create(Request $request, string $token)
    {
        return Inertia::render('Auth/ResetPassword', [
            'token' => $token,
            'email' => $request->query('email'),
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'token'    => ['required'],
            'email'    => ['required', 'email'],
            'password' => ['required', 'confirmed', 'min:8'],
        ]);

        $credentials = $request->only('email', 'password', 'password_confirmation', 'token');

        $resetUser = function ($user) use ($request) {
            $user->forceFill([
                'password' => Hash::make($request->string('password')),
                'remember_token' => Str::random(60),
            ])->save();

            event(new PasswordReset($user));
        };

        $status = Password::reset($credentials, $resetUser);

        // Seller-approval invite links are minted via a separate, longer-lived
        // broker (see SellerApplicationApprover) but land on this same page/form —
        // if the default broker doesn't recognize the token, this is the other
        // place it could legitimately have come from.
        if ($status !== Password::PASSWORD_RESET) {
            $status = Password::broker('seller_invite')->reset($credentials, $resetUser);
        }

        if ($status === Password::PASSWORD_RESET) {
            return redirect('/login')->with('status', __($status));
        }

        return back()->withErrors([
            'email' => [__($status)],
        ]);
    }
}
