<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Services\Scrydex\ScrydexClient;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(ScrydexClient::class, fn () => new ScrydexClient(
            baseUrl: config('services.scrydex.url'),
            apiKey:  config('services.scrydex.key'),
            teamId:  config('services.scrydex.team_id'),
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
