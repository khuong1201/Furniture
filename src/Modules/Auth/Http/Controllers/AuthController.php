<?php

namespace Modules\Auth\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Modules\Auth\Services\AuthService;
use Modules\Auth\Domain\Repositories\AuthRepositoryInterface;
use Modules\Auth\Http\Requests\RegisterRequest;
use Modules\Auth\Http\Requests\LoginRequest;
use Modules\Auth\Http\Requests\RefreshTokenRequest;

class AuthController
{
    public function __construct(
        private AuthService $authService,
        private AuthRepositoryInterface $authRepository
    ) {}

    public function register(RegisterRequest $request)
    {
        $data = $request->validated();

        $user = $this->authRepository->create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => bcrypt($data['password']),
        ]);

        // Attach default role
        if (class_exists(\Modules\Role\Domain\Models\Role::class)) {
            $role = \Modules\Role\Domain\Models\Role::where('name', 'user')->first();
            if ($role) {
                $user->roles()->attach($role);
            }
        }

        $accessToken = $this->authService->createAccessToken($user);
        $refreshToken = $this->authService->createRefreshToken(
            $user,
            $data['device_name'] ?? null,
            $request->ip(),
            $request->userAgent()
        );

        return response()->json([
            'access_token' => $accessToken,
            'refresh_token' => $refreshToken->token,
            'user' => $user->only(['id', 'uuid', 'email', 'name']),
        ], 201);
    }

    public function login(LoginRequest $request)
    {
        $data = $request->validated();

        $user = $this->authRepository->findByEmail($data['email']);
        if (!$user || !Hash::check($data['password'], $user->password)) {
            return response()->json(['message' => 'Invalid credentials'], 401);
        }

        $accessToken = $this->authService->createAccessToken($user);
        $refreshToken = $this->authService->createRefreshToken(
            $user,
            $data['device_name'] ?? null,
            $request->ip(),
            $request->userAgent()
        );

        return response()->json([
            'access_token' => $accessToken,
            'refresh_token' => $refreshToken->token,
            'permissions' => method_exists($user, 'permissions')
                ? $user->permissions()->pluck('name')
                : [],
        ]);
    }

    public function refresh(RefreshTokenRequest $request)
    {
        $token = $request->validated()['refresh_token'];
        $newToken = $this->authService->rotateRefreshToken($token);

        if (!$newToken) {
            return response()->json(['message' => 'Invalid or expired refresh token'], 401);
        }

        return response()->json([
            'access_token' => $this->authService->createAccessToken($newToken->user),
            'refresh_token' => $newToken->token,
        ]);
    }

    public function logout(Request $request)
    {
        $token = $request->input('refresh_token');
        if ($token) {
            $this->authService->revokeRefreshToken($token);
        }

        return response()->json(['message' => 'Logged out successfully']);
    }
}
