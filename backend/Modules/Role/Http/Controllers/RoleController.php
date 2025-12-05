<?php

declare(strict_types=1);

namespace Modules\Role\Http\Controllers;

use Modules\Shared\Http\Controllers\BaseController;
use Modules\Shared\Http\Resources\ApiResponse;
use Modules\Role\Services\RoleService;
use Modules\Role\Http\Requests\StoreRoleRequest;
use Modules\Role\Http\Requests\UpdateRoleRequest;
use Modules\Role\Domain\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use OpenApi\Attributes as OA;

#[OA\Tag(name: "Roles", description: "Quản lý vai trò (RBAC)")]
class RoleController extends BaseController
{
    protected RoleService $roleService;

    public function __construct(RoleService $service)
    {
        parent::__construct($service);
        $this->roleService = $service;
    }

    #[OA\Get(
        path: "/admin/roles",
        summary: "Danh sách Roles",
        security: [['bearerAuth' => []]],
        tags: ["Roles"],
        parameters: [
            new OA\Parameter(name: "q", in: "query", description: "Search keyword", schema: new OA\Schema(type: "string")),
            new OA\Parameter(name: "page", in: "query", schema: new OA\Schema(type: "integer")),
        ],
        responses: [
            new OA\Response(response: 200, description: "Success"),
            new OA\Response(response: 403, description: "Forbidden")
        ]
    )]
    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', Role::class);
        
        $filters = $request->only(['q']);
        $perPage = $request->integer('per_page', 15);
        
        $data = $this->roleService->getRolesPaginated($filters, $perPage);

        return response()->json(ApiResponse::paginated($data));
    }

    #[OA\Post(
        path: "/admin/roles",
        summary: "Tạo Role mới",
        security: [['bearerAuth' => []]],
        tags: ["Roles"],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ["name"],
                properties: [
                    new OA\Property(property: "name", type: "string", example: "editor"),
                    new OA\Property(property: "description", type: "string"),
                    new OA\Property(property: "priority", type: "integer")
                ]
            )
        ),
        responses: [
            new OA\Response(response: 201, description: "Created"),
            new OA\Response(response: 422, description: "Validation Error")
        ]
    )]
    public function store(StoreRoleRequest $request): JsonResponse 
    {
        $this->authorize('create', Role::class);

        $data = $this->roleService->create($request->validated());

        return response()->json(ApiResponse::success($data, 'Role created successfully', 201), 201);
    }

    #[OA\Get(
        path: "/admin/roles/{uuid}",
        summary: "Chi tiết Role",
        security: [['bearerAuth' => []]],
        tags: ["Roles"],
        parameters: [new OA\Parameter(name: "uuid", in: "path", required: true, schema: new OA\Schema(type: "string"))],
        responses: [
            new OA\Response(response: 200, description: "Success"),
            new OA\Response(response: 404, description: "Not Found")
        ]
    )]
    public function show(string $uuid): JsonResponse
    {
        $role = $this->roleService->findByUuidOrFail($uuid);
        $this->authorize('view', $role);
        
        return response()->json(ApiResponse::success($role->load('permissions')));
    }

    #[OA\Put(
        path: "/admin/roles/{uuid}",
        summary: "Cập nhật Role",
        security: [['bearerAuth' => []]],
        tags: ["Roles"],
        parameters: [new OA\Parameter(name: "uuid", in: "path", required: true, schema: new OA\Schema(type: "string"))],
        requestBody: new OA\RequestBody(
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: "name", type: "string"),
                    new OA\Property(property: "description", type: "string"),
                ]
            )
        ),
        responses: [
            new OA\Response(response: 200, description: "Updated"),
            new OA\Response(response: 403, description: "Forbidden (System Role)")
        ]
    )]
    public function update(UpdateRoleRequest $request, string $uuid): JsonResponse
    {
        $role = $this->roleService->findByUuidOrFail($uuid);
        $this->authorize('update', $role);

        $data = $this->roleService->update($uuid, $request->validated());

        return response()->json(ApiResponse::success($data, 'Role updated successfully'));
    }

    #[OA\Delete(
        path: "/admin/roles/{uuid}",
        summary: "Xóa Role",
        security: [['bearerAuth' => []]],
        tags: ["Roles"],
        parameters: [new OA\Parameter(name: "uuid", in: "path", required: true, schema: new OA\Schema(type: "string"))],
        responses: [
            new OA\Response(response: 200, description: "Deleted"),
            new OA\Response(response: 403, description: "Forbidden (System Role)")
        ]
    )]
    public function destroy(string $uuid): JsonResponse
    {
        $role = $this->roleService->findByUuidOrFail($uuid);
        $this->authorize('delete', $role);

        $this->roleService->delete($uuid);

        return response()->json(ApiResponse::success(null, 'Role deleted successfully'));
    }
}