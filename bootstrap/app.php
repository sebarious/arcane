<?php

use App\Http\Middleware\AuthenticateStoreApiToken;
use App\Http\Middleware\EnforceStoreDailyApiLimit;
use App\Http\Middleware\EnsureKioskConfigured;
use App\Http\Middleware\EnsureMarkAsSoldEnabled;
use App\Http\Middleware\EnsureSellerStoreIsPublic;
use App\Http\Middleware\HandleInertiaRequests;
use App\Http\Middleware\LogStoreApiRequest;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Spatie\Permission\Middleware\RoleMiddleware;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        channels: __DIR__.'/../routes/channels.php',
        health: '/up',
    )
    ->withSchedule(function (Schedule $schedule): void {
        $schedule->command('arcane:prune-api-logs')->daily();
        // Keeps kiosk prices (110% of market value) reasonably fresh without
        // relying on BatchGenerator happening to run — see config/kiosk.php.
        $schedule->command('arcane:refresh-prices')->daily();
        $schedule->command('arcane:expire-kiosk-orders')->everyFifteenMinutes();
    })
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->web(append: [
            HandleInertiaRequests::class,
        ]);
        // Authenticity comes from the Stripe-Signature header (verified inside
        // StripeWebhookController), not a CSRF token Stripe has no way to send.
        $middleware->validateCsrfTokens(except: [
            'webhooks/stripe',
        ]);
        $middleware->alias([
            // existing aliases...
            'role' => RoleMiddleware::class,
            'store.live' => EnsureSellerStoreIsPublic::class,
            'store.api' => AuthenticateStoreApiToken::class,
            'store.api.daily-limit' => EnforceStoreDailyApiLimit::class,
            'store.api.mark-sold' => EnsureMarkAsSoldEnabled::class,
            'store.api.log' => LogStoreApiRequest::class,
            'kiosk.enabled' => EnsureKioskConfigured::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // api/* and webhooks/* are always JSON — nothing browses there
        // expecting HTML. kiosk/* is mixed: the page itself
        // (GET /kiosk) is a normal browser navigation and should 404 the same
        // branded way any other invalid URL on the site does (so a disabled
        // kiosk doesn't stand out as different from a plain 404), but its API
        // calls (search/basket/checkout, all sent with Accept: application/json)
        // need real JSON — without expectsJson() here, a ValidationException on
        // e.g. /kiosk/browse would redirect instead of returning JSON, since
        // Laravel's own detection gets overridden by the branded-error-page
        // logic below regardless of what Accept header the client actually sent.
        $isJsonOnly = fn (Request $request) => $request->is('api/*')
            || $request->is('webhooks/*')
            || ($request->is('kiosk/*') && $request->expectsJson());

        $exceptions->shouldRenderJsonWhen($isJsonOnly);

        // Branded 403/404/500/503 pages instead of Laravel's defaults — see
        // resources/ts/Pages/Errors/Error.vue.
        $exceptions->respond(function (SymfonyResponse $response, Throwable $exception, Request $request) use ($isJsonOnly) {
            if ($isJsonOnly($request)) {
                return $response;
            }

            $status = $response->getStatusCode();

            // 403/404/503 have no stack trace worth keeping — there's nothing to
            // debug, the route just didn't match or access was denied — so these
            // show the branded page in every environment, including local. 500 is
            // the one that matters while developing: left alone in local/testing
            // so Ignition's real stack trace still shows. Use
            // Debug\ErrorPagePreviewController's routes to preview any of these
            // locally without needing to trigger a real error.
            $alwaysBranded = in_array($status, [403, 404, 503], true);
            $brandedFiveHundred = $status === 500 && ! app()->environment(['local', 'testing']);

            if ($alwaysBranded || $brandedFiveHundred) {
                return Inertia::render('Errors/Error', ['status' => $status])
                    ->toResponse($request)
                    ->setStatusCode($status);
            }

            return $response;
        });
    })->create();
