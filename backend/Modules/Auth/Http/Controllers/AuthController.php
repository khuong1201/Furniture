<?php

namespace Modules\Auth\Http\Controllers;

use Illuminate\Http\Request;
use Modules\Shared\Http\Controllers\BaseController; 
use Modules\Shared\Http\Resources\ApiResponse;
use Modules\Auth\Services\AuthService;
use Modules\Auth\Http\Requests\RegisterRequest;
use Modules\Auth\Http\Requests\LoginRequest;
use Modules\Auth\Http\Requests\RefreshTokenRequest;

class AuthController extends BaseController 
{
    public function __construct(
        protected AuthService $authService
    ) {}

    public function register(RegisterRequest $request)
    {
        $result = $this->authService->register(
            $request->validated(),
            $request->ip(),
            $request->userAgent()
        );

        return response()->json(ApiResponse::success($result, 'Registration successful', 201), 201);
    }

    public function verify(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'otp' => 'required|string|size:6',
        ]);

        $result = $this->authService->verifyOtp($request->email, $request->otp);

        return response()->json($result);
    }

    public function login(LoginRequest $request)
    {
        $result = $this->authService->login(
            $request->input('email'),
            $request->input('password'),
            $request->input('device_name'),
            $request->ip(),
            $request->userAgent()
        );

        return response()->json(ApiResponse::success($result, 'Login successful'));
    }

    public function refresh(RefreshTokenRequest $request)
    {
        try {
            $result = $this->authService->rotateRefreshToken($request->input('refresh_token'));
            return response()->json(ApiResponse::success($result, 'Token refreshed'));
        } catch (\Exception $e) {
            return response()->json(ApiResponse::error($e->getMessage(), 401), 401);
        }
    }

    public function logout(Request $request)
    {
        $token = $request->input('refresh_token');
        if ($token) {
            $this->authService->revokeRefreshToken($token);
        }

        return response()->json(ApiResponse::success(null, 'Logged out successfully'));
    }
}