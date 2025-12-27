<?php

declare(strict_types=1);

namespace Modules\Role\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Role\Domain\Models\Role;
use Modules\Role\Http\Requests\StoreRoleRequest;
use Modules\Role\Http\Requests\UpdateRoleRequest;
use Modules\Role\Services\RoleService;
use Modules\Shared\Http\Controllers\BaseController;
use OpenApi\Attributes as OA;

#[OA\Tag(name: "Roles", description: "Quản lý vai trò & Phân quyền (RBAC)")]
class RoleController extends BaseController
{
    public function __construct(protected RoleService $roleService)
    {
        parent::__construct($roleService);
    }

    #[OA\Get(
        path: "/api/admin/roles",
        summary: "Danh sách Roles",
        security: [['bearerAuth' => []]],
        tags: ["Roles"],
        parameters: [
            new OA\Parameter(name: "page", in: "query", schema: new OA\Schema(type: "integer")),
            new OA\Parameter(name: "q", in: "query", description: "Tìm theo tên", schema: new OA\Schema(type: "string"))
        ],
        responses: [new OA\Response(response: 200, description: "OK")]
    )]
    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', Role::class);

        $filters = $request->only(['q']);
        $data = $this->roleService->paginate($request->integer('per_page', 15), $filters);

        return $this->successResponse($data);
    }

    #[OA\Post(
        path: "/api/admin/roles",
        summary: "Tạo Role mới",
        security: [['bearerAuth' => []]],
        tags: ["Roles"],
        requestBody: new OA\RequestBody(content: new OA\JsonContent(properties: [
            new OA\Property(property: "name", type: "string", example: "Manager"),
            new OA\Property(property: "description", type: "string"),
            new OA\Property(property: "priority", type: "integer", default: 0)
        ])),
        responses: [
            new OA\Response(response: 201, description: "Created"),
            new OA\Response(response: 409, description: "Duplicate Name (Code: 409191)")
        ]
    )]
    public function store(StoreRoleRequest $request): JsonResponse
    {
        $this->authorize('create', Role::class);

        $role = $this->roleService->create($request->validated());

        return $this->successResponse($role, 'Role created successfully', 201);
    }

    #[OA\Get(
        path: "/api/admin/roles/{uuid}",
        summary: "Chi tiết Role",
        security: [['bearerAuth' => []]],
        tags: ["Roles"],
        parameters: [new OA\Parameter(name: "uuid", in: "path", required: true, schema: new OA\Schema(type: "string"))],
        responses: [new OA\Response(response: 200, description: "OK")]
    )]
    public function show(string $uuid): JsonResponse
    {
        $role = $this->roleService->findByUuidOrFail($uuid);
        $this->authorize('view', $role);

        return $this->successResponse($role->load('permissions'));
    }

    #[OA\Put(
        path: "/api/admin/roles/{uuid}",
        summary: "Cập nhật Role",
        security: [['bearerAuth' => []]],
        tags: ["Roles"],
        parameters: [new OA\Parameter(name: "uuid", in: "path", required: true)],
        responses: [new OA\Response(response: 200, description: "Updated")]
    )]
    public function update(UpdateRoleRequest $request, string $uuid): JsonResponse
    {
        $role = $this->roleService->findByUuidOrFail($uuid);
        $this->authorize('update', $role);

        $updated = $this->roleService->update($uuid, $request->validated());

        return $this->successResponse($updated, 'Role updated successfully');
    }

    #[OA\Delete(
        path: "/api/admin/roles/{uuid}",
        summary: "Xóa Role",
        security: [['bearerAuth' => []]],
        tags: ["Roles"],
        parameters: [new OA\Parameter(name: "uuid", in: "path", required: true)],
        responses: [
            new OA\Response(response: 200, description: "Deleted"),
            new OA\Response(response: 403, description: "System Role (Code: 403192)")
        ]
    )]
    public function destroy(string $uuid): JsonResponse
    {
        $role = $this->roleService->findByUuidOrFail($uuid);
        $this->authorize('delete', $role);

        $this->roleService->delete($uuid);

        return $this->successResponse(null, 'Role deleted successfully');
    }
}