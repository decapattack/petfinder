<?php

namespace App\Providers;

use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Fix #11: Force HTTPS in production to enable geolocation API in browsers
        if ($this->app->environment('production')) {
            URL::forceScheme('https');
        }
    }
}
