<?php

declare(strict_types=1);

namespace Modules\User\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Shared\Http\Controllers\BaseController;
use Modules\User\Domain\Models\User;
use Modules\User\Http\Requests\StoreUserRequest;
use Modules\User\Http\Requests\UpdateUserRequest;
use Modules\User\Services\UserService;
use OpenApi\Attributes as OA;

#[OA\Tag(name: "Users", description: "API quản lý người dùng")]
class UserController extends BaseController
{
    public function __construct(protected UserService $userService)
    {
        parent::__construct($userService);
    }

    #[OA\Get(
        path: "/api/admin/users",
        summary: "Xem danh sách",
        tags: ["Users"],
        parameters: [
            new OA\Parameter(name: "page", in: "query", schema: new OA\Schema(type: "integer")),
            new OA\Parameter(name: "q", in: "query", description: "Search keyword", schema: new OA\Schema(type: "string")),
            new OA\Parameter(name: "is_active", in: "query", schema: new OA\Schema(type: "boolean")),
            new OA\Parameter(name: "role_id", in: "query", schema: new OA\Schema(type: "integer")),
        ],
        responses: [
            new OA\Response(response: 200, description: "Success")
        ]
    )]
    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', User::class);
        
        $filters = $request->only(['q', 'is_active', 'role_id']);
        $data = $this->userService->filter($filters);

        return $this->successResponse($data);
    }

    #[OA\Post(
        path: "/api/admin/users",
        summary: "Tạo người dùng",
        tags: ["Users"],
        requestBody: new OA\RequestBody(
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: "name", type: "string"),
                    new OA\Property(property: "email", type: "string"),
                    new OA\Property(property: "password", type: "string"),
                    new OA\Property(property: "role_id", type: "integer"),
                ]
            )
        ),
        responses: [
            new OA\Response(response: 201, description: "Created")
        ]
    )]
    public function store(StoreUserRequest $request): JsonResponse
    {
        $this->authorize('create', User::class);

        $user = $this->userService->create($request->validated());

        return $this->successResponse($user, 'User created successfully', 201);
    }

    #[OA\Get(
        path: "/api/admin/users/{uuid}",
        summary: "Xem chi tiết",
        tags: ["Users"],
        parameters: [
            new OA\Parameter(name: "uuid", in: "path", required: true, schema: new OA\Schema(type: "string"))
        ],
        responses: [
            new OA\Response(response: 200, description: "Success")
        ]
    )]
    public function show(string $uuid): JsonResponse
    {
        $user = $this->userService->findByUuidOrFail($uuid);
        $this->authorize('view', $user);

        return $this->successResponse($user->load('roles.permissions'));
    }

    #[OA\Put(
        path: "/api/admin/users/{uuid}",
        summary: "Cập nhật Admin",
        tags: ["Users"],
        parameters: [
            new OA\Parameter(name: "uuid", in: "path", required: true, schema: new OA\Schema(type: "string"))
        ],
        requestBody: new OA\RequestBody(
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: "name", type: "string"),
                    new OA\Property(property: "is_active", type: "boolean"),
                ]
            )
        ),
        responses: [
            new OA\Response(response: 200, description: "Updated")
        ]
    )]
    public function update(UpdateUserRequest $request, string $uuid): JsonResponse
    {
        $user = $this->userService->findByUuidOrFail($uuid);
        $this->authorize('update', $user);

        $data = $request->validated();
        
        if (!$request->user()->hasPermissionTo('user.edit')) {
            unset($data['roles'], $data['is_active'], $data['email']);
        }

        $updatedUser = $this->userService->update($uuid, $data);

        return $this->successResponse($updatedUser, 'User updated successfully');
    }

    #[OA\Delete(
        path: "/api/admin/users/{uuid}",
        summary: "Xóa người dùng",
        tags: ["Users"],
        parameters: [
            new OA\Parameter(name: "uuid", in: "path", required: true, schema: new OA\Schema(type: "string"))
        ],
        responses: [
            new OA\Response(response: 200, description: "Deleted")
        ]
    )]
    public function destroy(string $uuid): JsonResponse
    {
        $user = $this->userService->findByUuidOrFail($uuid);
        
        $this->authorize('delete', $user);

        $this->userService->delete($uuid);

        return $this->successResponse(null, 'User deleted successfully');
    }

    #[OA\Get(
        path: "/api/profile",
        summary: "Thông tin cá nhân",
        tags: ["Users"],
        responses: [
            new OA\Response(response: 200, description: "Success")
        ]
    )]
    public function profile(Request $request): JsonResponse
    {
        $user = $request->user();
        $user->avatar_url = $user->getFirstMediaUrl('avatar'); 
        
        return $this->successResponse($user->load(['roles', 'roles.permissions']));
    }

    #[OA\Put(
        path: "/api/profile",
        summary: "Cập nhật hồ sơ cá nhân",
        tags: ["Users"],
        requestBody: new OA\RequestBody(
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: "name", type: "string"),
                    new OA\Property(property: "phone", type: "string"),
                ]
            )
        ),
        responses: [
            new OA\Response(response: 200, description: "Updated")
        ]
    )]
    public function updateProfile(Request $request): JsonResponse
    {
        $user = $request->user();

        $validated = $request->validate([
            'name'  => 'required|string|max:255',
            'phone' => 'nullable|string|max:20|unique:users,phone,' . $user->id,
        ]);

        $this->userService->update($user->uuid, $validated);

        return $this->successResponse($user, 'Cập nhật hồ sơ thành công');
    }

    #[OA\Post(
        path: "/api/auth/change-password",
        summary: "Đổi mật khẩu",
        tags: ["Users"],
        requestBody: new OA\RequestBody(
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: "current_password", type: "string"),
                    new OA\Property(property: "password", type: "string"),
                    new OA\Property(property: "password_confirmation", type: "string"),
                ]
            )
        ),
        responses: [
            new OA\Response(response: 200, description: "Password Changed")
        ]
    )]
    public function changePassword(Request $request): JsonResponse
    {
        $request->validate([
            'current_password' => 'required|string',
            'password'         => 'required|string|min:6|confirmed',
        ]);

        $this->userService->changePassword(
            $request->user(),
            $request->input('current_password'),
            $request->input('password')
        );

        return $this->successResponse(null, 'Đổi mật khẩu thành công');
    }
}