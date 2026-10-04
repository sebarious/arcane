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

        /*
         * Kiosk limiters.
         *
         * These exist as named limiters rather than inline `throttle:n,1`
         * because the inline form keys guests on `domain|ip` alone, with no
         * per-route component (ThrottleRequests::resolveRequestSignature()).
         * Every inline-throttled route on the site therefore shares ONE
         * counter and only the ceiling differs — so a payment's status poll
         * (roughly 30 hits a minute) would burn through the allowance that
         * /kiosk/checkout, capped at 10, then reads, and the next customer's
         * payment died with "Too Many Attempts.".
         *
         * They key on the session, not the IP, for a second reason: every
         * tablet in the shop shares one public IP, so an IP-keyed limit is
         * really a shop-wide limit, and the till, the catalogue tablet and a
         * customer's phone on the wifi all eat each other's budget.
         */
        $perTablet = fn (Request $request, string $bucket) => $bucket.'|'.$request->session()->getId();

        RateLimiter::for('kiosk-browse', fn (Request $request) => Limit::perMinute(120)->by($perTablet($request, 'kiosk-browse')));
        RateLimiter::for('kiosk-basket', fn (Request $request) => Limit::perMinute(60)->by($perTablet($request, 'kiosk-basket')));
        RateLimiter::for('kiosk-checkout', fn (Request $request) => Limit::perMinute(10)->by($perTablet($request, 'kiosk-checkout')));
        // Polled every 2s while the reader waits; 3 minutes of that is ~90
        // hits, so this needs real headroom of its own.
        RateLimiter::for('kiosk-status', fn (Request $request) => Limit::perMinute(90)->by($perTablet($request, 'kiosk-status')));
        RateLimiter::for('kiosk-order', fn (Request $request) => Limit::perMinute(30)->by($perTablet($request, 'kiosk-order')));

        // The PIN screen stays keyed on IP on purpose: this one is guarding
        // against guessing, and a guesser can clear a cookie to get a fresh
        // session whenever they like.
        RateLimiter::for('kiosk-unlock', fn (Request $request) => Limit::perMinute(20)->by('kiosk-unlock|'.$request->ip()));
    }
}
