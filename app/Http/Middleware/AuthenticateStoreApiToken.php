<?php

namespace App\Http\Middleware;

use App\Enums\ApiMode;
use App\Models\Store;
use App\Services\Api\SandboxBatchProvisioner;
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
    public function __construct(private SandboxBatchProvisioner $sandboxProvisioner) {}

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

        // Lazily provision this store's sandbox batch the first time it makes
        // a call in test mode — every endpoint then works exactly as it would
        // live, just against fixture data. See SandboxBatchProvisioner.
        if ($store->api_mode === ApiMode::Test) {
            $this->sandboxProvisioner->ensure($store);
        }

        $request->attributes->set('api_store', $store);

        $response = $next($request);
        $response->headers->set('X-Api-Mode', $store->api_mode->value);

        return $response;
    }
}
