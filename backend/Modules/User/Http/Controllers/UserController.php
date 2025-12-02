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
    description: "API quản lý người dùng"
)]
class UserController extends BaseController
{
    public function __construct(UserService $service)
    {
        parent::__construct($service);
    }

    #[OA\Get(
        path: "/admin/users",
        summary: "Xem danh sách người dùng (Admin)",
        security: [['bearerAuth' => []]],
        tags: ["Users (Admin & Self)"],
        parameters: [
            new OA\Parameter(name: "page", in: "query", schema: new OA\Schema(type: "integer")),
            new OA\Parameter(name: "per_page", in: "query", schema: new OA\Schema(type: "integer")),
        ],
        responses: [
            new OA\Response(response: 200, description: "Success"),
            new OA\Response(response: 403, description: "Forbidden")
        ]
    )]
    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', User::class);
        
        return parent::index($request); 
    }   

    #[OA\Post(
        path: "/admin/users",
        summary: "Tạo người dùng mới (Admin)",
        security: [['bearerAuth' => []]],
        tags: ["Users (Admin & Self)"],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ["name", "email", "password"],
                properties: [
                    new OA\Property(property: "name", type: "string"),
                    new OA\Property(property: "email", type: "string", format: "email"),
                    new OA\Property(property: "password", type: "string", format: "password"),
                    new OA\Property(property: "roles", type: "array", items: new OA\Items(type: "string")),
                ]
            )
        ),
        responses: [
            new OA\Response(response: 201, description: "Created"),
            new OA\Response(response: 403, description: "Forbidden")
        ]
    )]

    public function store(StoreUserRequest $request): JsonResponse
    {
        $this->authorize('create', User::class);

        $data = $request->validated();
        
        $user = $this->service->create($data);

        return response()->json(ApiResponse::success($user, 'User created successfully', 201), 201);
    }

    #[OA\Get(
        path: "/admin/users/{uuid}",
        summary: "Xem chi tiết người dùng",
        security: [['bearerAuth' => []]],
        tags: ["Users (Admin & Self)"],
        parameters: [
            new OA\Parameter(name: "uuid", in: "path", required: true, schema: new OA\Schema(type: "string", format: "uuid"))
        ],
        responses: [
            new OA\Response(response: 200, description: "Success"),
            new OA\Response(response: 403, description: "Forbidden (Not Owner/Admin)"),
            new OA\Response(response: 404, description: "Not Found")
        ]
    )]

    public function show(string $uuid): JsonResponse
    {
        $user = $this->service->findByUuidOrFail($uuid);

        $this->authorize('view', $user);

        return response()->json(ApiResponse::success($user));
    }

    #[OA\Get(
        path: "/profile",
        summary: "Xem thông tin người dùng hiện tại",
        security: [['bearerAuth' => []]],
        tags: ["Users (Admin & Self)"],
        responses: [
            new OA\Response(
                response: 200,
                description: "Success",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "success", type: "boolean"),
                        new OA\Property(property: "data", type: "object",
                            properties: [
                                new OA\Property(property: "uuid", type: "string"),
                                new OA\Property(property: "name", type: "string"),
                                new OA\Property(property: "email", type: "string"),
                                new OA\Property(property: "roles", type: "array", items: new OA\Items(type: "string")),
                            ]
                        )
                    ]
                )
            ),
            new OA\Response(response: 404, description: "User not found")
        ]
    )]
    
    public function profile(Request $request): JsonResponse
    {
        $user = $this->service->findByUuidOrFail($uuid);

        $this->authorize('view', $user);

        return response()->json(ApiResponse::success($user));
    }

    #[OA\Put(
        path: "/admin/users/{uuid}",
        summary: "Cập nhật thông tin",
        security: [['bearerAuth' => []]],
        tags: ["Users (Admin & Self)"],
        parameters: [
            new OA\Parameter(name: "uuid", in: "path", required: true, schema: new OA\Schema(type: "string", format: "uuid"))
        ],
        requestBody: new OA\RequestBody(
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: "name", type: "string"),
                    new OA\Property(property: "roles", type: "array", items: new OA\Items(type: "string")),
                ]
            )
        ),
        responses: [
            new OA\Response(response: 200, description: "Updated"),
            new OA\Response(response: 403, description: "Forbidden")
        ]
    )]

    public function update(UpdateUserRequest $request, string $uuid): JsonResponse
    {
        $user = $this->service->findByUuidOrFail($uuid);
        
        $this->authorize('update', $user);

        $data = $request->validated();

        if (!$request->user()->hasPermissionTo('user.edit')) {
             unset($data['roles']);   
             unset($data['is_active']);
             unset($data['email']);     
        }

        $updatedUser = $this->service->update($uuid, $data);

        return response()->json(ApiResponse::success($updatedUser, 'User updated successfully'));
    }

    #[OA\Delete(
        path: "/admin/users/{uuid}",
        summary: "Xóa người dùng (Admin)",
        security: [['bearerAuth' => []]],
        tags: ["Users (Admin & Self)"],
        parameters: [
            new OA\Parameter(name: "uuid", in: "path", required: true, schema: new OA\Schema(type: "string", format: "uuid"))
        ],
        responses: [
            new OA\Response(response: 200, description: "Deleted")
        ]
    )]
    public function destroy(string $uuid): JsonResponse
    {
        $user = $this->service->findByUuidOrFail($uuid);

        $this->authorize('delete', $user);

        $this->service->delete($uuid);

        return response()->json(ApiResponse::success(null, 'User deleted successfully'));
    }
}