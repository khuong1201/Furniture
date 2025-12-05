<?php

declare(strict_types=1);

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

#[OA\Tag(name: "Users", description: "API quản lý người dùng")]
class UserController extends BaseController
{
    protected UserService $userService;

    public function __construct(UserService $service)
    {
        parent::__construct($service);
        $this->userService = $service;
    }

    #[OA\Get(
        path: "/admin/users",
        summary: "Xem danh sách người dùng",
        security: [['bearerAuth' => []]],
        tags: ["Users"],
        parameters: [
            new OA\Parameter(name: "page", in: "query", schema: new OA\Schema(type: "integer")),
            new OA\Parameter(name: "per_page", in: "query", schema: new OA\Schema(type: "integer")),
            new OA\Parameter(name: "q", in: "query", description: "Search keyword", schema: new OA\Schema(type: "string")),
            new OA\Parameter(name: "is_active", in: "query", schema: new OA\Schema(type: "boolean")),
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
        summary: "Tạo người dùng",
        security: [['bearerAuth' => []]],
        tags: ["Users"],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ["name", "email", "password"],
                properties: [
                    new OA\Property(property: "name", type: "string"),
                    new OA\Property(property: "email", type: "string", format: "email"),
                    new OA\Property(property: "password", type: "string", format: "password"),
                    new OA\Property(property: "is_active", type: "boolean"),
                    new OA\Property(property: "roles", type: "array", items: new OA\Items(type: "integer"))
                ]
            )
        ),
        responses: [
            new OA\Response(response: 201, description: "Created"),
            new OA\Response(response: 422, description: "Validation Error"),
            new OA\Response(response: 403, description: "Forbidden")
        ]
    )]
    public function store(StoreUserRequest $request): JsonResponse
    {
        $this->authorize('create', User::class);
        $user = $this->userService->create($request->validated());
        return response()->json(ApiResponse::success($user, 'User created successfully', 201), 201);
    }

    #[OA\Get(
        path: "/admin/users/{uuid}",
        summary: "Xem chi tiết người dùng",
        security: [['bearerAuth' => []]],
        tags: ["Users"],
        parameters: [
            new OA\Parameter(name: "uuid", in: "path", required: true, schema: new OA\Schema(type: "string", format: "uuid"))
        ],
        responses: [
            new OA\Response(response: 200, description: "Success"),
            new OA\Response(response: 404, description: "Not Found"),
            new OA\Response(response: 403, description: "Forbidden")
        ]
    )]
    public function show(string $uuid): JsonResponse
    {
        $user = $this->userService->findByUuidOrFail($uuid);
        $this->authorize('view', $user);
        return response()->json(ApiResponse::success($user->load('roles')));
    }

    #[OA\Get(
        path: "/profile",
        summary: "Xem thông tin cá nhân",
        security: [['bearerAuth' => []]],
        tags: ["Users"],
        responses: [
            new OA\Response(response: 200, description: "Success"),
            new OA\Response(response: 401, description: "Unauthenticated")
        ]
    )]
    public function profile(Request $request): JsonResponse
    {
        $user = $request->user();
        
        if (!$user) {
             return response()->json(ApiResponse::error('Unauthorized', 401), 401);
        }

        return response()->json(ApiResponse::success($user->load(['roles', 'roles.permissions'])));
    }

    #[OA\Put(
        path: "/admin/users/{uuid}",
        summary: "Cập nhật thông tin",
        security: [['bearerAuth' => []]],
        tags: ["Users"],
        parameters: [
            new OA\Parameter(name: "uuid", in: "path", required: true, schema: new OA\Schema(type: "string", format: "uuid"))
        ],
        requestBody: new OA\RequestBody(
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: "name", type: "string"),
                    new OA\Property(property: "password", type: "string"),
                    new OA\Property(property: "is_active", type: "boolean"),
                    new OA\Property(property: "roles", type: "array", items: new OA\Items(type: "integer"))
                ]
            )
        ),
        responses: [
            new OA\Response(response: 200, description: "Updated"),
            new OA\Response(response: 403, description: "Forbidden"),
            new OA\Response(response: 404, description: "Not Found")
        ]
    )]
    public function update(UpdateUserRequest $request, string $uuid): JsonResponse
    {
        $user = $this->userService->findByUuidOrFail($uuid);
        $this->authorize('update', $user);

        $data = $request->validated();
        $currentUser = $request->user();

        if (!$currentUser->hasPermissionTo('user.edit')) {
             unset($data['roles']);   
             unset($data['is_active']);
             unset($data['email']);     
        }

        $updatedUser = $this->userService->update($uuid, $data);
        return response()->json(ApiResponse::success($updatedUser, 'User updated successfully'));
    }

    #[OA\Delete(
        path: "/admin/users/{uuid}",
        summary: "Xóa người dùng",
        security: [['bearerAuth' => []]],
        tags: ["Users"],
        parameters: [
            new OA\Parameter(name: "uuid", in: "path", required: true, schema: new OA\Schema(type: "string", format: "uuid"))
        ],
        responses: [
            new OA\Response(response: 200, description: "Deleted"),
            new OA\Response(response: 403, description: "Forbidden")
        ]
    )]
    public function destroy(string $uuid): JsonResponse
    {
        $user = $this->userService->findByUuidOrFail($uuid);
        $this->authorize('delete', $user);

        if ($user->id === request()->user()->id) {
            return response()->json(ApiResponse::error('Cannot delete yourself', 403), 403);
        }

        $this->userService->delete($uuid);
        return response()->json(ApiResponse::success(null, 'User deleted successfully'));
    }
}