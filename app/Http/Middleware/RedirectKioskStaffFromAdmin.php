<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Keeps kiosk-role staff out of the admin panel proper.
 *
 * They sign in through the normal admin login — that's the only account
 * system there is — but the panel is not theirs: they get bounced straight to
 * the kiosk access page, which is all their role is for.
 *
 * Enforced here, on the panel's own middleware stack, rather than by turning
 * off each resource in turn. A single gate can't be forgotten: a resource
 * added next month is behind it automatically, whereas a per-resource check
 * is one missed override away from exposing the whole till.
 */
class RedirectKioskStaffFromAdmin
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        // Admins keep full access even if they also hold the kiosk role.
        if (! $user || ! $user->hasRole('kiosk') || $user->hasRole('admin')) {
            return $next($request);
        }

        // Logging out has to stay reachable, or they'd be stuck on the
        // kiosk page with no way to sign out of a shared tablet.
        if ($request->routeIs('filament.admin.auth.logout')) {
            return $next($request);
        }

        return redirect()->route('kiosk.access');
    }
}
