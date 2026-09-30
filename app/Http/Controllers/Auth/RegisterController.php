<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

/**
 * Plain email/password registration — the only self-service account
 * creation path that isn't role-gated (unlike the seller/affiliate flows
 * elsewhere in the app). Immediate login, no approval step: anyone should be
 * able to just buy a Digital Rip. Primarily used from AuthModal.vue, but
 * works as a normal form post too.
 */
class RegisterController extends Controller
{
    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'confirmed', 'min:8'],
        ]);

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
        ]);

        Auth::login($user);

        // Same AJAX-friendly branch as LoginController — AuthModal.vue stays
        // on the current page and reloads the auth prop itself.
        if ($request->wantsJson()) {
            return response()->json(['data' => ['id' => $user->id]]);
        }

        // A real Inertia form post (Auth/Login.vue's register mode) — "back"
        // would just be the login page itself, so send them somewhere new.
        return redirect('/');
    }
}
