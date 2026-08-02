<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Services\PulseApi\PulseApiClient;

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
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
