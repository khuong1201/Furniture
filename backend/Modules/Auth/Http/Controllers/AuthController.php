<?php

namespace Modules\Auth\Http\Controllers;

use Illuminate\Http\Request;
use Modules\Shared\Http\Controllers\BaseController;
use Modules\Shared\Http\Resources\ApiResponse;
use Modules\Auth\Services\AuthService;
use Modules\Auth\Http\Requests\RegisterRequest;
use Modules\Auth\Http\Requests\LoginRequest;
use Modules\Auth\Http\Requests\RefreshTokenRequest;
use OpenApi\Attributes as OA;

#[OA\Tag(
    name: "Auth",
    description: "Quản lý xác thực người dùng (Login, Register, OTP...)"
)]

class AuthController extends BaseController
{
    public function __construct(
        protected AuthService $authService
    ) {
    }

    #[OA\Post(
        path: "/auth/register",
        summary: "Đăng ký tài khoản mới",
        description: "Tạo tài khoản và gửi OTP qua email. Chưa trả về Token đăng nhập ngay.",
        tags: ["Auth"],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ["name", "email", "password", "password_confirmation"],
                properties: [
                    new OA\Property(property: "name", type: "string", example: "Nguyen Van A"),
                    new OA\Property(property: "email", type: "string", format: "email", example: "user@example.com"),
                    new OA\Property(property: "password", type: "string", format: "password", example: "password123"),
                    new OA\Property(property: "password_confirmation", type: "string", example: "password123"),
                    new OA\Property(property: "device_name", type: "string", example: "iphone_13", nullable: true)
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 201,
                description: "Đăng ký thành công, chờ xác thực OTP",
                content: new OA\JsonContent(
                    allOf: [
                        new OA\Schema(ref: "#/components/schemas/ApiResponse"),
                        new OA\Schema(properties: [
                            new OA\Property(property: "data", type: "object", properties: [
                                new OA\Property(property: "message", type: "string", example: "Please check your email..."),
                                new OA\Property(property: "require_verification", type: "boolean", example: true)
                            ])
                        ])
                    ]
                )
            ),
            new OA\Response(response: 422, description: "Lỗi Validation (Email trùng, mật khẩu yếu...)")
        ]
    )]


    public function register(RegisterRequest $request)
    {
        $result = $this->authService->register(
            $request->validated(),
            $request->ip(),
            $request->userAgent()
        );

        return response()->json(ApiResponse::success($result, 'Registration successful', 201), 201);
    }
    #[OA\Post(
        path: "/auth/verify",
        summary: "Xác thực OTP",
        description: "Nhập Email và mã OTP 6 số để kích hoạt tài khoản và lấy Token.",
        tags: ["Auth"],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ["email", "otp"],
                properties: [
                    new OA\Property(property: "email", type: "string", format: "email", example: "user@example.com"),
                    new OA\Property(property: "otp", type: "string", example: "123456")
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: "Xác thực thành công, trả về Token",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "access_token", type: "string", example: "eyJ0eXAi..."),
                        new OA\Property(property: "refresh_token", type: "string", example: "def456..."),
                        new OA\Property(property: "expires_in", type: "integer", example: 3600),
                        new OA\Property(property: "user", type: "object")
                    ]
                )
            ),
            new OA\Response(response: 400, description: "Sai OTP hoặc OTP hết hạn")
        ]
    )]

    public function verify(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'otp' => 'required|string|size:6',
        ]);

        $result = $this->authService->verifyOtp($request->email, $request->otp);

        return response()->json($result);
    }

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
                    new OA\Property(property: "password", type: "string", format: "password", example: "123456"),
                    new OA\Property(property: "device_name", type: "string", example: "web_chrome")
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: "Login thành công",
                content: new OA\JsonContent(
                    allOf: [
                        new OA\Schema(ref: "#/components/schemas/ApiResponse"),
                        new OA\Schema(properties: [
                            new OA\Property(property: "data", type: "object", properties: [
                                new OA\Property(property: "user", type: "object", properties: [
                                    new OA\Property(property: "id", type: "integer", example: 1),
                                    new OA\Property(property: "uuid", type: "string", example: "123e4567-e89b-12d3-a456-426614174000"),
                                    new OA\Property(property: "email", type: "string", example: "admin@system.com"),
                                    new OA\Property(property: "name", type: "string", example: "Super Admin")
                                ]),
                                new OA\Property(property: "access_token", type: "string"),
                                new OA\Property(property: "refresh_token", type: "string"),
                                new OA\Property(property: "roles", type: "array", items: new OA\Items(type: "string"), example: ["admin"]),
                                new OA\Property(property: "permissions", type: "array", items: new OA\Items(type: "string")),
                                new OA\Property(property: "expires_in", type: "integer", example: 3600)
                            ])
                        ])
                    ]
                )
            ),
            new OA\Response(response: 401, description: "Sai tài khoản hoặc mật khẩu")
        ]
    )]

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

    #[OA\Post(
        path: "/auth/refresh",
        summary: "Làm mới Token (Refresh Token)",
        tags: ["Auth"],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ["refresh_token"],
                properties: [
                    new OA\Property(property: "refresh_token", type: "string", example: "abcdef123456...")
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: "Token mới được cấp",
                content: new OA\JsonContent(
                    allOf: [
                        new OA\Schema(ref: "#/components/schemas/ApiResponse"),
                        new OA\Schema(properties: [
                            new OA\Property(property: "data", type: "object", properties: [
                                new OA\Property(property: "access_token", type: "string"),
                                new OA\Property(property: "refresh_token", type: "string")
                            ])
                        ])
                    ]
                )
            ),
            new OA\Response(response: 401, description: "Refresh Token không hợp lệ hoặc hết hạn")
        ]
    )]

    public function refresh(RefreshTokenRequest $request)
    {
        try {
            $result = $this->authService->rotateRefreshToken($request->input('refresh_token'));
            return response()->json(ApiResponse::success($result, 'Token refreshed'));
        } catch (\Exception $e) {
            return response()->json(ApiResponse::error($e->getMessage(), 401), 401);
        }
    }

    #[OA\Post(
        path: "/auth/logout",
        summary: "Đăng xuất",
        description: "Thu hồi Refresh Token hiện tại.",
        tags: ["Auth"],
        security: [["bearerAuth" => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ["refresh_token"],
                properties: [
                    new OA\Property(property: "refresh_token", type: "string")
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: "Đăng xuất thành công",
                content: new OA\JsonContent(ref: "#/components/schemas/ApiResponse")
            )
        ]
    )]

    public function logout(Request $request)
    {
        $token = $request->input('refresh_token');
        if ($token) {
            $this->authService->revokeRefreshToken($token);
        }

        return response()->json(ApiResponse::success(null, 'Logged out successfully'));
    }
}