<?php

declare(strict_types=1);

namespace Modules\Auth\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Modules\Shared\Http\Controllers\BaseController;
use Modules\Shared\Http\Resources\ApiResponse;
use Modules\Auth\Services\AuthService;
use Modules\Auth\Http\Requests\LoginRequest;
use Modules\Auth\Http\Requests\RegisterRequest;
use Exception;
use OpenApi\Attributes as OA;

#[OA\Tag(name: "Auth", description: "Xác thực & Phân quyền")]
class AuthController extends BaseController
{
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
            new OA\Response(response: 200, description: "Login Successful"),
            new OA\Response(response: 422, description: "Validation Error"),
            new OA\Response(response: 401, description: "Invalid credentials")
        ]
    )]
    public function login(Request $request): JsonResponse
    {
        try {
            // Validate thủ công để catch lỗi
            $validator = Validator::make($request->all(), (new LoginRequest)->rules());

            if ($validator->fails()) {
                throw new ValidationException($validator);
            }

            $data = $this->authService->login(
                $validator->validated(), 
                $request->input('device_name', 'web')
            );

            return response()->json(ApiResponse::success($data, 'Đăng nhập thành công'));

        } catch (ValidationException $e) {
            return response()->json(ApiResponse::error($e->getMessage(), 422, $e->errors()), 422);
        } catch (Exception $e) {
            // Bắt lỗi BusinessException từ Service (VD: Tài khoản bị khóa)
            $code = $e->getCode() ?: 500;
            return response()->json(ApiResponse::error($e->getMessage(), $code), $code);
        }
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
    public function register(Request $request): JsonResponse
    {
        try {
            // [FIX LỖI 200 OK]: Validate thủ công
            $validator = Validator::make($request->all(), (new RegisterRequest)->rules());

            if ($validator->fails()) {
                throw new ValidationException($validator);
            }

            $data = $this->authService->register(
                $validator->validated(),
                $request->input('device_name', 'web')
            );

            return response()->json(ApiResponse::success($data, 'Đăng ký thành công', 201), 201);

        } catch (ValidationException $e) {
            // Trả về JSON lỗi 422 rõ ràng cho Frontend
            return response()->json(ApiResponse::error('Dữ liệu không hợp lệ', 422, $e->errors()), 422);
        } catch (Exception $e) {
            return response()->json(ApiResponse::error('Lỗi hệ thống: ' . $e->getMessage(), 500), 500);
        }
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
    public function refresh(Request $request): JsonResponse
    {
        try {
            $request->validate(['refresh_token' => 'required|string']);

            $data = $this->authService->refreshToken(
                $request->input('refresh_token'),
                $request->input('device_name', 'web')
            );

            return response()->json(ApiResponse::success($data, 'Làm mới token thành công'));
        } catch (Exception $e) {
            return response()->json(ApiResponse::error($e->getMessage(), 401), 401);
        }
    }

    #[OA\Post(
        path: "/auth/verify-email",
        summary: "Xác thực tài khoản bằng OTP",
        security: [['bearerAuth' => []]],
        tags: ["Auth"],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ["otp"],
                properties: [
                    new OA\Property(property: "otp", type: "string", example: "123456", description: "Mã 6 số nhận được qua email")
                ]
            )
        ),
        responses: [
            new OA\Response(response: 200, description: "Email verified successfully"),
            new OA\Response(response: 400, description: "Invalid OTP")
        ]
    )]
    public function verifyEmail(Request $request): JsonResponse
    {
        // Validate đầu vào
        $request->validate([
            'otp' => 'required|string|size:6', // Bắt buộc 6 ký tự
        ]);

        try {
            $this->authService->verifyEmail($request->user()->id, $request->input('otp'));
            
            return response()->json(ApiResponse::success(null, 'Xác thực email thành công.'));
        } catch (BusinessException $e) {
            return response()->json(ApiResponse::error($e->getMessage(), $e->getCode()));
        }
    }

    #[OA\Post(
        path: "/auth/resend-verification",
        summary: "Gửi lại mã OTP",
        security: [['bearerAuth' => []]],
        tags: ["Auth"],
        responses: [
            new OA\Response(response: 200, description: "OTP resent"),
            new OA\Response(response: 400, description: "Email already verified")
        ]
    )]
    public function resendVerification(Request $request): JsonResponse
    {
        try {
            $this->authService->resendOtp($request->user()->id);
            
            return response()->json(ApiResponse::success(null, 'Mã xác thực mới đã được gửi vào email của bạn.'));
        } catch (BusinessException $e) {
            return response()->json(ApiResponse::error($e->getMessage(), $e->getCode()));
        }
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