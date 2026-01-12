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

        // Check if user is admin
        if (!$user->is_admin) {
            Log::warning('Non-admin login attempt', [
                'user_id' => $user->id,
                'ip' => $request->ip(),
            ]);

            throw ValidationException::withMessages([
                'email' => ['You do not have permission to access this resource.'],
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
