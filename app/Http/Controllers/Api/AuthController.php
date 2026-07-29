<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\LicenseService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function __construct(
        private readonly LicenseService $licenseService,
    ) {
    }

    /**
     * Login and get API token.
     */
    public function login(Request $request)
    {
        $request->validate([
            'username' => 'required|string',
            'password' => 'required|string',
            'device_name' => 'nullable|string',
        ]);

        $user = User::where('username', $request->username)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'username' => ['Les identifiants fournis sont incorrects.'],
            ]);
        }

        // Check if user is active
        if (!$user->isActive()) {
            throw ValidationException::withMessages([
                'username' => ['Votre compte a été bloqué. Contactez l\'administrateur.'],
            ]);
        }

        if ($this->licenseService->isExpiredOrMissing()) {
            throw ValidationException::withMessages([
                'username' => [$this->licenseService->clientBlockMessage()],
            ]);
        }

        $deviceName = $request->device_name ?? 'api-client';
        $token = $user->createToken($deviceName)->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'Connexion réussie.',
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'username' => $user->username,
                'role' => $user->role,
                'status' => $user->status,
            ],
            'token' => $token,
            'token_type' => 'Bearer',
        ]);
    }

    /**
     * Logout and revoke token.
     */
    public function logout(Request $request)
    {
        // Revoke current token
        /** @var \Laravel\Sanctum\PersonalAccessToken $token */
        $token = $request->user()->currentAccessToken();
        $token->delete();

        return response()->json([
            'success' => true,
            'message' => 'Déconnexion réussie.',
        ]);
    }

    /**
     * Get authenticated user info.
     */
    public function user(Request $request)
    {
        $user = $request->user();

        return response()->json([
            'success' => true,
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'username' => $user->username,
                'role' => $user->role,
                'status' => $user->status,
                'created_at' => $user->created_at,
            ],
        ]);
    }

    /**
     * Refresh token (revoke current and create new).
     */
    public function refresh(Request $request)
    {
        $user = $request->user();
        /** @var \Laravel\Sanctum\PersonalAccessToken $currentToken */
        $currentToken = $request->user()->currentAccessToken();
        $deviceName = $currentToken->name ?? 'api-client';

        // Revoke current token
        $currentToken->delete();

        // Create new token
        $token = $user->createToken($deviceName)->plainTextToken;

        return response()->json([
            'success' => true,
            'token' => $token,
            'token_type' => 'Bearer',
        ]);
    }
}
