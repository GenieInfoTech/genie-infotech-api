<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    /**
     * Login and get API token
     *
     * SECURITY:
     * - Rate limited via ThrottleLogin middleware
     * - Brute force protection
     * - IP logging
     */
    public function login(Request $request): JsonResponse
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
            'device_name' => 'nullable|string|max:255',
        ]);

        $user = User::where('email', strtolower($request->email))->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            Log::warning('Failed login attempt', [
                'email' => $request->email,
                'ip' => $request->ip(),
            ]);

            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        // Update login info
        $user->updateLoginInfo($request->ip());

        // Create token
        $deviceName = $request->device_name ?? 'api-token';
        $token = $user->createToken($deviceName, ['*'], now()->addHours(24));

        Log::info('Successful login', [
            'user_id' => $user->id,
            'ip' => $request->ip(),
        ]);

        return response()->json([
            'success' => true,
            'token' => $token->plainTextToken,
            'expires_at' => $token->accessToken->expires_at,
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'is_admin' => $user->is_admin,
            ],
        ]);
    }

    /**
     * Register a new user account
     *
     * SECURITY:
     * - Email validation
     * - Strong password requirements
     * - IP logging
     */
    public function register(Request $request): JsonResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email|max:255',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => strtolower($request->email),
            'password' => Hash::make($request->password),
            'is_admin' => false,
            'last_login_ip' => $request->ip(),
            'last_login_at' => now(),
        ]);

        // Create token
        $deviceName = $request->device_name ?? 'api-token';
        $token = $user->createToken($deviceName, ['*'], now()->addHours(24));

        Log::info('New user registered', [
            'user_id' => $user->id,
            'ip' => $request->ip(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Registration successful',
            'token' => $token->plainTextToken,
            'expires_at' => $token->accessToken->expires_at,
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'is_admin' => $user->is_admin,
            ],
        ], 201);
    }

    /**
     * Logout and revoke tokens
     */
    public function logout(Request $request): JsonResponse
    {
        // Revoke current token
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'success' => true,
            'message' => 'Logged out successfully.',
        ]);
    }
}
