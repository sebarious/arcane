<?php

namespace App\Http\Middleware;

use App\Models\ApiDailyUsage;
use App\Models\Store;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Runs after AuthenticateStoreApiToken (needs the resolved store) and enforces
 * Store::daily_request_limit — a per-store quota, unlike the flat per-minute
 * rate limiter in routes/api.php. Backed by App\Models\ApiDailyUsage rather
 * than the cache-based RateLimiter so usage survives cache flushes and can be
 * shown back to the seller (calls used today, 30-day graph).
 */
class EnforceStoreDailyApiLimit
{
    public function handle(Request $request, Closure $next): Response
    {
        /** @var Store $store */
        $store = $request->attributes->get('api_store');

        if (! ApiDailyUsage::attemptIncrement($store)) {
            return response()->json([
                'message' => 'Daily request limit reached. Resets at midnight UTC.',
            ], 429);
        }

        return $next($request);
    }
}
