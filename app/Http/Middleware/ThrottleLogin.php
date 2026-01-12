<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Cache\RateLimiter;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Throttle Login Attempts Middleware
 *
 * SECURITY: Prevents brute force attacks on login
 */
class ThrottleLogin
{
    protected RateLimiter $limiter;

    public function __construct(RateLimiter $limiter)
    {
        $this->limiter = $limiter;
    }

    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $key = $this->resolveRequestSignature($request);
        $maxAttempts = (int) env('LOGIN_RATE_LIMIT', 5);
        $decayMinutes = 15; // Lock out for 15 minutes

        if ($this->limiter->tooManyAttempts($key, $maxAttempts)) {
            $seconds = $this->limiter->availableIn($key);

            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Too many login attempts. Please try again in ' . ceil($seconds / 60) . ' minutes.',
                    'retry_after' => $seconds,
                ], 429);
            }

            abort(429, 'Too many login attempts. Please try again later.');
        }

        $response = $next($request);

        // If login failed (401 or validation error), increment attempts
        if ($response->getStatusCode() === 401 || $response->getStatusCode() === 422) {
            $this->limiter->hit($key, $decayMinutes * 60);
        } else {
            // Clear attempts on successful login
            $this->limiter->clear($key);
        }

        return $response;
    }

    /**
     * Resolve the request signature for rate limiting.
     */
    protected function resolveRequestSignature(Request $request): string
    {
        // Rate limit by IP + email combination
        $email = $request->input('email', '');
        return sha1($request->ip() . '|' . strtolower($email) . '|login');
    }
}
