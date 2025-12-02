<?php

namespace Modules\User\Http\Controllers;

use Modules\Shared\Http\Controllers\BaseController;
use Modules\Shared\Http\Resources\ApiResponse;
use Modules\User\Services\UserService;
use Modules\User\Domain\Models\User;
use Modules\User\Http\Requests\StoreUserRequest;
use Modules\User\Http\Requests\UpdateUserRequest;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use OpenApi\Attributes as OA;

#[OA\Tag(
    name: "Users (Admin & Self)",
    description: "API quản lý người dùng (Chủ yếu Admin, User tự sửa profile)"
)]

class UserController extends BaseController
{
    public function __construct(UserService $service)
    {
        parent::__construct($service);
    }

    #[OA\Get(
        path: "/api/admin/users",
        summary: "Xem danh sách người dùng (Admin)",
        security: [['bearerAuth' => []]],
        tags: ["Users (Admin & Self)"],
        parameters: [
            new OA\Parameter(name: "page", in: "query", schema: new OA\Schema(type: "integer")),
            new OA\Parameter(name: "per_page", in: "query", schema: new OA\Schema(type: "integer")),
        ],
        responses: [
            new OA\Response(response: 200, description: "Successful operation"),
            new OA\Response(response: 403, description: "Forbidden (Not Admin)")
        ]
    )]

    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', User::class);
        
        return parent::index($request); 
    }   

    #[OA\Post(
        path: "/api/admin/users",
        summary: "Tạo người dùng mới (Admin)",
        security: [['bearerAuth' => []]],
        tags: ["Users (Admin & Self)"],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ["name", "email", "password"],
                properties: [
                    new OA\Property(property: "name", type: "string", example: "New Admin"),
                    new OA\Property(property: "email", type: "string", format: "email", example: "new.admin@example.com"),
                    new OA\Property(property: "password", type: "string", format: "password", example: "SecureP@ss123"),
                    new OA\Property(property: "roles", type: "array", description: "Mảng các UUID hoặc tên Role", items: new OA\Items(type: "string", example: "admin")),
                ]
            )
        ),
        responses: [
            new OA\Response(response: 201, description: "User created"),
            new OA\Response(response: 403, description: "Forbidden (Not Admin)")
        ]
    )]

    public function store(Request $request): JsonResponse
    {
        $this->authorize('create', User::class);

        $validatedData = app(StoreUserRequest::class)->validated();

        $data = $this->service->create($validatedData);

        return response()->json(ApiResponse::success($data, 'User created successfully', 201), 201);
    }

    #[OA\Get(
        path: "/api/admin/users/{uuid}",
        summary: "Xem chi tiết người dùng (Admin hoặc Chính chủ)",
        security: [['bearerAuth' => []]],
        tags: ["Users (Admin & Self)"],
        parameters: [
            new OA\Parameter(name: "uuid", in: "path", required: true, schema: new OA\Schema(type: "string", format: "uuid"))
        ],
        responses: [
            new OA\Response(response: 200, description: "Successful operation"),
            new OA\Response(response: 403, description: "Forbidden (Không phải Admin hoặc chính chủ)"),
            new OA\Response(response: 404, description: "User not found")
        ]
    )]

    public function show(string $uuid): JsonResponse
    {
        $user = $this->service->getRepository()->findByUuid($uuid);
        if (!$user) {
            return response()->json(ApiResponse::error('User not found', 404), 404);
        }

        $this->authorize('view', $user);

        return response()->json(ApiResponse::success($user));
    }

    #[OA\Put(
        path: "/api/admin/users/{uuid}",
        summary: "Cập nhật thông tin người dùng (Admin hoặc Chính chủ)",
        security: [['bearerAuth' => []]],
        tags: ["Users (Admin & Self)"],
        parameters: [
            new OA\Parameter(name: "uuid", in: "path", required: true, schema: new OA\Schema(type: "string", format: "uuid"))
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: "name", type: "string", example: "Jane Doe"),
                    new OA\Property(property: "phone", type: "string", nullable: true, example: "0901234567"),
                    new OA\Property(property: "password", type: "string", format: "password", nullable: true, description: "Chỉ điền nếu muốn đổi password"),
                    new OA\Property(property: "roles", type: "array", description: "Mảng các UUID hoặc tên Role (Chỉ Admin mới được sửa role)", items: new OA\Items(type: "string", example: "editor")),
                ]
            )
        ),
        responses: [
            new OA\Response(response: 200, description: "User updated"),
            new OA\Response(response: 403, description: "Forbidden"),
            new OA\Response(response: 404, description: "User not found")
        ]
    )]

    public function update(Request $request, string $uuid): JsonResponse
    {
        $user = $this->service->getRepository()->findByUuid($uuid);
        if (!$user) {
            return response()->json(ApiResponse::error('User not found', 404), 404);
        }

        $this->authorize('update', $user);

        $validatedData = app(UpdateUserRequest::class)->validated();

        $data = $this->service->update($uuid, $validatedData);

        return response()->json(ApiResponse::success($data, 'User updated successfully'));
    }

    #[OA\Delete(
        path: "/api/admin/users/{uuid}",
        summary: "Xóa người dùng (Admin)",
        security: [['bearerAuth' => []]],
        tags: ["Users (Admin & Self)"],
        parameters: [
            new OA\Parameter(name: "uuid", in: "path", required: true, schema: new OA\Schema(type: "string", format: "uuid"))
        ],
        responses: [
            new OA\Response(response: 200, description: "User deleted"),
            new OA\Response(response: 403, description: "Forbidden (Không phải Admin hoặc đang cố xóa chính mình)"),
            new OA\Response(response: 404, description: "User not found")
        ]
    )]
    
    public function destroy(string $uuid): JsonResponse
    {
        $user = $this->service->getRepository()->findByUuid($uuid);
        if (!$user) {
            return response()->json(ApiResponse::error('User not found', 404), 404);
        }

        $this->authorize('delete', $user);

        $this->service->delete($uuid);

        return response()->json(ApiResponse::success(null, 'User deleted successfully'));
    }
}