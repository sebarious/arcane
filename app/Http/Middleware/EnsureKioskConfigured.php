<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Hides the entire kiosk (page + API) behind a plain 404 until Stripe is
 * actually configured — there's no point exposing a checkout flow that can't
 * take payment, and a bare 404 (rather than a branded/broken page) doesn't
 * advertise that the feature exists yet.
 */
class EnsureKioskConfigured
{
    public function handle(Request $request, Closure $next): Response
    {
        if (blank(config('services.stripe.secret'))) {
            abort(404);
        }

        return $next($request);
    }
}
