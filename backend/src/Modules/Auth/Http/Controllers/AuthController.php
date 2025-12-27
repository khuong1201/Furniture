<?php

declare(strict_types=1);

namespace Modules\Auth\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Auth\Http\Requests\LoginRequest;
use Modules\Auth\Http\Requests\RegisterRequest;
use Modules\Auth\Http\Requests\RefreshTokenRequest;
use Modules\Auth\Services\AuthService;
use Modules\Shared\Http\Controllers\BaseController;
use OpenApi\Attributes as OA;

#[OA\Tag(name: "Auth", description: "Xác thực & Phân quyền")]
class AuthController extends BaseController
{
    public function __construct(AuthService $authService)
    {
        parent::__construct($authService);
    }

    #[OA\Post(path: "/auth/login", summary: "Đăng nhập", tags: ["Auth"],
        requestBody: new OA\RequestBody(content: new OA\JsonContent(properties: [
            new OA\Property(property: "email", type: "string", example: "admin@system.com"),
            new OA\Property(property: "password", type: "string", example: "password"),
            new OA\Property(property: "device_name", type: "string", example: "web")
        ])),
        responses: [
            new OA\Response(response: 200, description: "OK"),
            new OA\Response(response: 401, description: "Sai thông tin (Code: 401020)"),
            new OA\Response(response: 423, description: "Bị khóa (Code: 423014)")
        ]
    )]
    public function login(LoginRequest $request): JsonResponse
    {
        $data = $this->service->login(
            $request->input('email'),
            $request->input('password'),
            $request->input('device_name', 'web'),
            $request->ip(),         
            $request->userAgent()   
        );

        return $this->successResponse($data, 'Đăng nhập thành công');
    }

    #[OA\Post(path: "/auth/register", summary: "Đăng ký", tags: ["Auth"],
        requestBody: new OA\RequestBody(content: new OA\JsonContent(properties: [
            new OA\Property(property: "name", type: "string"),
            new OA\Property(property: "email", type: "string"),
            new OA\Property(property: "password", type: "string"),
            new OA\Property(property: "password_confirmation", type: "string")
        ])),
        responses: [
            new OA\Response(response: 201, description: "Created"),
            new OA\Response(response: 409, description: "Email trùng (Code: 409011)")
        ]
    )]
    public function register(RegisterRequest $request): JsonResponse
    {
        $data = $this->service->register(
            $request->validated(),
            $request->input('device_name', 'web'),
            $request->ip(),
            $request->userAgent()
        );

        return $this->successResponse($data, 'Đăng ký thành công', 201);
    }

    #[OA\Post(path: "/auth/refresh", summary: "Refresh Token", tags: ["Auth"],
        requestBody: new OA\RequestBody(content: new OA\JsonContent(properties: [
            new OA\Property(property: "refresh_token", type: "string"),
            new OA\Property(property: "device_name", type: "string")
        ])),
        responses: [new OA\Response(response: 200, description: "OK")]
    )]
    public function refresh(RefreshTokenRequest $request): JsonResponse
    {
        $data = $this->service->refreshToken(
            $request->input('refresh_token'),
            $request->input('device_name', 'web'),
            $request->ip(),
            $request->userAgent()
        );

        return $this->successResponse($data, 'Làm mới token thành công');
    }

    #[OA\Post(path: "/auth/verify-email", summary: "Verify OTP", tags: ["Auth"],
        requestBody: new OA\RequestBody(content: new OA\JsonContent(properties: [
            new OA\Property(property: "otp", type: "string")
        ])),
        responses: [new OA\Response(response: 200, description: "OK")]
    )]
    public function verifyEmail(Request $request): JsonResponse
    {
        $request->validate(['otp' => 'required|string|size:6']);

        $this->service->verifyEmail($request->user()->id, $request->input('otp'));

        return $this->successResponse(null, 'Xác thực email thành công.');
    }

    #[OA\Post(path: "/auth/resend-verification", summary: "Resend OTP", tags: ["Auth"],
        responses: [new OA\Response(response: 200, description: "OK")]
    )]
    public function resendVerification(Request $request): JsonResponse
    {
        $this->service->resendOtp($request->user()->id);
        return $this->successResponse(null, 'Mã xác thực mới đã được gửi.');
    }

    #[OA\Post(path: "/auth/logout", summary: "Logout", tags: ["Auth"],
        responses: [new OA\Response(response: 200, description: "OK")]
    )]
    public function logout(Request $request): JsonResponse
    {
        $this->service->logout($request->user());
        return $this->successResponse(null, 'Đăng xuất thành công');
    }

    #[OA\Get(path: "/auth/me", summary: "Get Profile", tags: ["Auth"],
        responses: [new OA\Response(response: 200, description: "OK")]
    )]
    public function me(Request $request): JsonResponse
    {
        $user = $request->user()->load(['roles', 'roles.permissions']);
        $user->avatar_url = $user->getFirstMediaUrl('avatar'); 

        return $this->successResponse($user);
    }
}