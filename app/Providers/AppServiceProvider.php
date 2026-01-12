<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
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
        // Configure rate limiting
        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute((int) env('API_RATE_LIMIT', 60))
                ->by($request->user()?->id ?: $request->ip());
        });

        // Stricter rate limiting for login attempts
        RateLimiter::for('login', function (Request $request) {
            return Limit::perMinute((int) env('LOGIN_RATE_LIMIT', 5))
                ->by($request->ip());
        });

        // Rate limiting for contact form
        RateLimiter::for('contact', function (Request $request) {
            return Limit::perMinute(10)->by($request->ip());
        });
    }
}
