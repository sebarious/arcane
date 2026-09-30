<?php

namespace App\Http\Controllers\Kiosk;

use App\Http\Controllers\Controller;
use App\Http\Middleware\EnsureKioskUnlocked;
use App\Services\Kiosk\KioskDailyPin;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;

/**
 * Where kiosk-role staff land after signing in: today's PIN, and a button
 * that opens the till without making them type it.
 */
class StaffAccessController extends Controller
{
    public function show(KioskDailyPin $pin): InertiaResponse
    {
        return Inertia::render('Kiosk/StaffAccess', [
            'pin' => $pin->for(),
            'alreadyUnlocked' => session(EnsureKioskUnlocked::SESSION_KEY) === $pin->for(),
        ]);
    }

    /**
     * Unlocks this browser and goes straight to the till.
     *
     * Deliberately doesn't ask for the PIN back: the PIN exists to prove
     * someone is staff, and an authenticated kiosk account has already
     * proved exactly that. Typing a code they can see on the same screen
     * would be theatre.
     */
    public function apply(Request $request, KioskDailyPin $pin): RedirectResponse
    {
        $request->session()->put(EnsureKioskUnlocked::SESSION_KEY, $pin->for());

        return redirect()->route('kiosk.index');
    }
}
