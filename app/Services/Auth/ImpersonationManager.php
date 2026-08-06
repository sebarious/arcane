<?php

namespace App\Services\Auth;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ImpersonationManager
{
    /** Admin starts viewing the site as another user, without knowing their password. */
    public function start(Request $request, User $target): void
    {
        abort_if($target->is($request->user()), 403, "You can't impersonate yourself.");
        abort_if($request->session()->has('impersonator_id'), 403, 'Already impersonating — stop first.');

        $request->session()->put('impersonator_id', $request->user()->id);
        Auth::login($target);
    }

    /** Ends impersonation and logs the original admin back in. Returns their id, or null if not impersonating. */
    public function stop(Request $request): ?int
    {
        $impersonatorId = $request->session()->pull('impersonator_id');

        if (! $impersonatorId) {
            return null;
        }

        Auth::loginUsingId($impersonatorId);

        // Filament's AuthenticateSession middleware stamps a password hash into the
        // session for whichever user it saw most recently. That stamp belongs to the
        // impersonated user and would mismatch the reinstated admin on their next
        // Filament request, force-logging them out. Clearing it lets the middleware
        // re-stamp against the now-current (correct) user instead.
        $request->session()->forget('password_hash_'.config('auth.defaults.guard'));

        return $impersonatorId;
    }

    public function redirectPathFor(User $user): string
    {
        return match (true) {
            $user->hasRole('admin') => '/admin',
            $user->hasRole('seller') => '/seller',
            default => '/',
        };
    }
}
