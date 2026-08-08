<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Models\ApiSetting;
use App\Services\PulseApi\PulseApiClient;
use App\Services\Vision\GoogleVisionClient;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(PulseApiClient::class, fn () => new PulseApiClient(
            baseUrl: config('services.pulseapi.url'),
            apiKey:  config('services.pulseapi.key'),
        ));

        $this->app->singleton(GoogleVisionClient::class, fn () => new GoogleVisionClient(
            apiKey: config('services.google_vision.key'),
        ));
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Seller API (routes/api.php) — this is the flat, global per-minute
        // abuse guard; it keys by the raw bearer token (not the resolved
        // store) so a bad/guessed token still gets capped without an extra DB
        // lookup here — AuthenticateStoreApiToken does the real lookup after.
        // Falls back to IP for requests with no token at all. The per-store
        // daily quota is enforced separately by EnforceStoreDailyApiLimit,
        // which is DB-backed (not cache-based) since it needs to survive
        // cache flushes and back the usage figures shown to the seller.
        RateLimiter::for('store-api-minute', function (Request $request) {
            $key = $request->bearerToken() ?? $request->ip();

            return Limit::perMinute(ApiSetting::current()->rate_limit_per_minute)
                ->by($key)
                ->response(fn () => response()->json([
                    'message' => 'Rate limit exceeded. Try again shortly.',
                ], 429));
        });
    }
}
