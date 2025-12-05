<?php

declare(strict_types=1);

namespace Modules\Auth\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Modules\Shared\Http\Controllers\BaseController;
use Modules\Shared\Http\Resources\ApiResponse;
use Modules\Auth\Services\AuthService;
use Modules\Auth\Http\Requests\LoginRequest;
use Modules\Auth\Http\Requests\RegisterRequest;
use Modules\Auth\Http\Requests\RefreshTokenRequest;
use OpenApi\Attributes as OA;

#[OA\Tag(name: "Auth", description: "Xác thực & Phân quyền")]
class AuthController extends BaseController
{
    // Lưu ý: AuthController không kế thừa logic CRUD của BaseService nên không cần inject vào parent
    public function __construct(
        protected AuthService $authService
    ) {}

    #[OA\Post(
        path: "/auth/login",
        summary: "Đăng nhập",
        tags: ["Auth"],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ["email", "password"],
                properties: [
                    new OA\Property(property: "email", type: "string", format: "email", example: "admin@system.com"),
                    new OA\Property(property: "password", type: "string", format: "password", example: "password"),
                    new OA\Property(property: "device_name", type: "string", example: "web")
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: "Login Successful",
                content: new OA\JsonContent(properties: [
                    new OA\Property(property: "success", type: "boolean", example: true),
                    new OA\Property(property: "data", type: "object", properties: [
                        new OA\Property(property: "access_token", type: "string"),
                        new OA\Property(property: "refresh_token", type: "string"),
                        new OA\Property(property: "token_type", type: "string", example: "Bearer"),
                        new OA\Property(property: "user", type: "object")
                    ])
                ])
            ),
            new OA\Response(response: 401, description: "Invalid credentials"),
            new OA\Response(response: 403, description: "Account inactive")
        ]
    )]
    public function login(LoginRequest $request): JsonResponse
    {
        $data = $this->authService->login(
            $request->validated(), 
            $request->input('device_name', 'web')
        );

        return response()->json(ApiResponse::success($data, 'Đăng nhập thành công'));
    }

    #[OA\Post(
        path: "/auth/register",
        summary: "Đăng ký tài khoản",
        tags: ["Auth"],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ["name", "email", "password", "password_confirmation"],
                properties: [
                    new OA\Property(property: "name", type: "string"),
                    new OA\Property(property: "email", type: "string", format: "email"),
                    new OA\Property(property: "password", type: "string", format: "password"),
                    new OA\Property(property: "password_confirmation", type: "string", format: "password"),
                    new OA\Property(property: "device_name", type: "string")
                ]
            )
        ),
        responses: [
            new OA\Response(response: 201, description: "Registered Successfully"),
            new OA\Response(response: 422, description: "Validation Error")
        ]
    )]
    public function register(RegisterRequest $request): JsonResponse
    {
        $data = $this->authService->register(
            $request->validated(),
            $request->input('device_name', 'web')
        );

        return response()->json(ApiResponse::success($data, 'Đăng ký thành công', 201), 201);
    }

    #[OA\Post(
        path: "/auth/refresh",
        summary: "Lấy Access Token mới bằng Refresh Token",
        tags: ["Auth"],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ["refresh_token"],
                properties: [
                    new OA\Property(property: "refresh_token", type: "string"),
                    new OA\Property(property: "device_name", type: "string")
                ]
            )
        ),
        responses: [
            new OA\Response(response: 200, description: "Token Refreshed"),
            new OA\Response(response: 401, description: "Invalid Refresh Token")
        ]
    )]
    public function refresh(RefreshTokenRequest $request): JsonResponse
    {
        $data = $this->authService->refreshToken(
            $request->validated('refresh_token'),
            $request->input('device_name', 'web')
        );

        return response()->json(ApiResponse::success($data, 'Token refreshed successfully'));
    }

    #[OA\Post(
        path: "/auth/logout",
        summary: "Đăng xuất",
        security: [['bearerAuth' => []]],
        tags: ["Auth"],
        responses: [
            new OA\Response(response: 200, description: "Logged out")
        ]
    )]
    public function logout(Request $request): JsonResponse
    {
        $this->authService->logout($request->user());
        return response()->json(ApiResponse::success(null, 'Đăng xuất thành công'));
    }

    #[OA\Get(
        path: "/auth/me",
        summary: "Thông tin hiện tại",
        security: [['bearerAuth' => []]],
        tags: ["Auth"],
        responses: [
            new OA\Response(response: 200, description: "Success")
        ]
    )]
    public function me(Request $request): JsonResponse
    {
        $user = $request->user()->load(['roles', 'roles.permissions']);
        return response()->json(ApiResponse::success($user));
    }
}