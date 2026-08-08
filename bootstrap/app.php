<?php

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
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->web(append: [
            \App\Http\Middleware\HandleInertiaRequests::class,
        ]);
        $middleware->alias([
            // existing aliases...
            'role' => RoleMiddleware::class,
            'store.live' => \App\Http\Middleware\EnsureSellerStoreIsPublic::class,
            'store.api' => \App\Http\Middleware\AuthenticateStoreApiToken::class,
            'store.api.daily-limit' => \App\Http\Middleware\EnforceStoreDailyApiLimit::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*'),
        );

        // Branded 403/404/500/503 pages instead of Laravel's defaults — see
        // resources/ts/Pages/Errors/Error.vue. Left alone in local/testing so
        // Ignition's debug page (with the actual stack trace) still shows —
        // use Debug\ErrorPagePreviewController's routes to preview the design
        // without needing to trigger a real error or flip APP_ENV.
        $exceptions->respond(function (SymfonyResponse $response, Throwable $exception, Request $request) {
            if ($request->is('api/*')) {
                return $response;
            }

            if (! app()->environment(['local', 'testing']) && in_array($response->getStatusCode(), [403, 404, 500, 503], true)) {
                return Inertia::render('Errors/Error', ['status' => $response->getStatusCode()])
                    ->toResponse($request)
                    ->setStatusCode($response->getStatusCode());
            }

            return $response;
        });
    })->create();
