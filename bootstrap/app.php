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
        // resources/ts/Pages/Errors/Error.vue.
        $exceptions->respond(function (SymfonyResponse $response, Throwable $exception, Request $request) {
            if ($request->is('api/*')) {
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
