<?php

namespace App\Http\Middleware;

use App\Models\Store;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Token auth for the seller-facing API (routes/api.php) — a Bearer token maps
 * straight to a Store, gated on both api_access_granted (admin-controlled
 * eligibility, see StoreResource) and api_enabled (the seller's own on/off
 * switch, see Seller\ApiAccessController). Either one being off blocks both
 * endpoints immediately, even mid-token-lifetime.
 */
class AuthenticateStoreApiToken
{
    public function handle(Request $request, Closure $next): Response
    {
        $token = $request->bearerToken();

        $store = $token
            ? Store::where('api_token', $token)
                ->where('api_access_granted', true)
                ->where('api_enabled', true)
                ->first()
            : null;

        if (! $store) {
            return response()->json(['message' => 'Invalid or missing API token.'], 401);
        }

        $request->attributes->set('api_store', $store);

        return $next($request);
    }
}
