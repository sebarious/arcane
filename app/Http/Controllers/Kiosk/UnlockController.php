<?php

namespace App\Http\Controllers\Kiosk;

use App\Http\Controllers\Controller;
use App\Http\Middleware\EnsureKioskUnlocked;
use App\Services\Kiosk\KioskDailyPin;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;

class UnlockController extends Controller
{
    public function show(Request $request, KioskDailyPin $pin): InertiaResponse|RedirectResponse
    {
        if ($request->session()->get(EnsureKioskUnlocked::SESSION_KEY) === $pin->for()) {
            return redirect()->route('kiosk.index');
        }

        return Inertia::render('Kiosk/Unlock');
    }

    public function store(Request $request, KioskDailyPin $pin): RedirectResponse
    {
        $validated = $request->validate([
            'pin' => ['required', 'string', 'max:12'],
        ]);

        // Six digits is only ever meant to stop the person holding the tablet,
        // so it needs a brake against someone sitting there working through
        // the range. Keyed per device, not per PIN attempt.
        $key = 'kiosk-unlock:'.$request->session()->getId();

        if (RateLimiter::tooManyAttempts($key, 10)) {
            throw ValidationException::withMessages([
                'pin' => 'Too many attempts — wait a minute and try again.',
            ]);
        }

        if (! $pin->matches($validated['pin'])) {
            RateLimiter::hit($key, 60);

            throw ValidationException::withMessages([
                'pin' => 'That PIN isn\'t right. Check today\'s code in the admin panel.',
            ]);
        }

        RateLimiter::clear($key);

        // Stored as the PIN itself rather than a boolean, so tomorrow's
        // rotation locks the tablet again without anyone having to do it.
        $request->session()->put(EnsureKioskUnlocked::SESSION_KEY, $pin->for());

        return redirect()->route('kiosk.index');
    }

    public function destroy(Request $request): RedirectResponse
    {
        $request->session()->forget(EnsureKioskUnlocked::SESSION_KEY);

        return redirect()->route('kiosk.unlock.show');
    }
}
