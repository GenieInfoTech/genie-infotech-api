<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\BlogController;
use App\Http\Controllers\Api\ContactController;
use App\Http\Controllers\Api\LeadController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| SECURITY: All routes are rate-limited by default
| Public routes have stricter limits than authenticated routes
|
*/

// Health check
Route::get('/health', fn () => response()->json(['status' => 'ok', 'timestamp' => now()->toIso8601String()]));

/*
|--------------------------------------------------------------------------
| Public Routes (Rate Limited: 60/min)
|--------------------------------------------------------------------------
*/
Route::middleware(['throttle:api'])->group(function () {
    // Contact form submission (with honeypot protection)
    Route::post('/contact', [ContactController::class, 'submit'])
        ->middleware('honeypot');

    // Blog endpoints (public read-only)
    Route::prefix('blog')->group(function () {
        Route::get('/', [BlogController::class, 'index']);
        Route::get('/categories', [BlogController::class, 'categories']);
        Route::get('/{slug}', [BlogController::class, 'show']);
    });

    // Lead capture (with honeypot protection)
    Route::post('/leads', [LeadController::class, 'store'])
        ->middleware('honeypot');
});

/*
|--------------------------------------------------------------------------
| Authentication Routes (Rate Limited: 5/min for login)
|--------------------------------------------------------------------------
*/
Route::middleware(['throttle.login'])->group(function () {
    Route::post('/auth/login', [AuthController::class, 'login']);
});

Route::post('/auth/logout', [AuthController::class, 'logout'])
    ->middleware('auth:sanctum');

/*
|--------------------------------------------------------------------------
| Protected Routes (Requires Authentication)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth:sanctum', 'throttle:api'])->group(function () {
    // Current user
    Route::get('/user', fn (Request $request) => $request->user());

    // Leads management (admin only)
    Route::middleware(['admin'])->group(function () {
        Route::get('/leads', [LeadController::class, 'index']);
        Route::get('/leads/{id}', [LeadController::class, 'show']);
        Route::put('/leads/{id}', [LeadController::class, 'update']);
        Route::delete('/leads/{id}', [LeadController::class, 'destroy']);
        Route::post('/leads/{id}/email', [LeadController::class, 'sendEmail']);
        Route::get('/leads/export/csv', [LeadController::class, 'export']);

        // Analytics
        Route::get('/analytics/leads', [LeadController::class, 'analytics']);
    });
});
