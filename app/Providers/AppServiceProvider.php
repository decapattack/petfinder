<?php

namespace App\Providers;

use App\Models\Alert;
use App\Models\HealthRecord;
use App\Models\Pet;
use App\Policies\AlertPolicy;
use App\Policies\HealthRecordPolicy;
use App\Policies\PetPolicy;
use Illuminate\Support\Facades\Gate;
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
        // Registrar Policies explicitamente
        Gate::policy(Pet::class, PetPolicy::class);
        Gate::policy(Alert::class, AlertPolicy::class);
        Gate::policy(HealthRecord::class, HealthRecordPolicy::class);

        // Fix #11: Force HTTPS in production to enable geolocation API in browsers
        if ($this->app->environment('production')) {
            URL::forceScheme('https');
        }
    }
}
