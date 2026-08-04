<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Services\PulseApi\PulseApiClient;
use App\Services\Vision\GoogleVisionClient;

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
        //
    }
}
