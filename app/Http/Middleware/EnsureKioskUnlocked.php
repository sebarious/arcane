<?php

namespace App\Http\Middleware;

use App\Services\Kiosk\KioskDailyPin;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Holds the kiosk shut until someone enters the day's PIN (shown in the admin
 * topbar). Without it the tablet is a till that anyone walking past can apply
 * a discount on — see KioskDailyPin.
 *
 * Applied to the kiosk's own pages and basket/checkout endpoints only. The
 * search/browse/filter lookups deliberately sit outside it: the public
 * catalogue calls those same URLs, and gating them would take the website
 * down with the tablet.
 */
class EnsureKioskUnlocked
{
    public const SESSION_KEY = 'kiosk_unlocked_for';

    public function handle(Request $request, Closure $next): Response
    {
        if (! config('kiosk.require_pin')) {
            return $next($request);
        }

        // Stored as the date it was unlocked for, so the tablet re-locks by
        // itself when the PIN rotates at midnight rather than staying open
        // indefinitely on one unlock.
        if ($request->session()->get(self::SESSION_KEY) === app(KioskDailyPin::class)->for()) {
            return $next($request);
        }

        if ($request->expectsJson()) {
            return response()->json(['message' => 'This kiosk is locked.', 'locked' => true], 423);
        }

        return redirect()->route('kiosk.unlock.show');
    }
}
