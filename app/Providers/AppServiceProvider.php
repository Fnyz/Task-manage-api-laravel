<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Support\Facades\RateLimiter;

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
        // configure custome limiters for the login and register routes
        RateLimiter::for('auth', function ($request){
            // return Limit::perMinute(5)->by($request->ip());
        });

        // configure custom limiters for the api routes
        RateLimiter::for('api', function ($request){
            // return Limit::perMinute(5)->by($request->ip());
        });
    }
}
