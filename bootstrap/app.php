<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        // Security Headers
        $middleware->append(\App\Http\Middleware\SecurityHeaders::class);

        // API middleware
        $middleware->api(prepend: [
            \Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful::class,
        ]);

        // Throttle API requests
        $middleware->throttleApi('api');

        // CSRF protection for web routes
        $middleware->validateCsrfTokens(except: [
            'api/*', // API routes use Sanctum tokens instead
        ]);

        // Alias for route middleware
        $middleware->alias([
            'auth' => \App\Http\Middleware\Authenticate::class,
            'admin' => \App\Http\Middleware\EnsureUserIsAdmin::class,
            'throttle.login' => \App\Http\Middleware\ThrottleLogin::class,
            'honeypot' => \Spatie\Honeypot\ProtectAgainstSpam::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        // Don't expose sensitive information in production
        $exceptions->shouldRenderJsonWhen(function ($request, $exception) {
            return $request->expectsJson() || $request->is('api/*');
        });
    })->create();
