<?php

declare(strict_types=1);

namespace Modules\Permission\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Permission\Domain\Models\Permission;
use Modules\Permission\Http\Requests\StorePermissionRequest;
use Modules\Permission\Http\Requests\UpdatePermissionRequest;
use Modules\Permission\Services\PermissionService;
use Modules\Shared\Http\Controllers\BaseController;
use OpenApi\Attributes as OA;

#[OA\Tag(name: "Permissions", description: "Quản lý danh sách quyền hạn")]
class PermissionController extends BaseController
{
    public function __construct(protected PermissionService $permissionService)
    {
        parent::__construct($permissionService);
    }

    #[OA\Get(
        path: "/api/admin/permissions",
        summary: "Danh sách Permissions",
        security: [['bearerAuth' => []]],
        tags: ["Permissions"],
        parameters: [
            new OA\Parameter(name: "page", in: "query", schema: new OA\Schema(type: "integer")),
            new OA\Parameter(name: "search", in: "query", description: "Tìm theo tên/module", schema: new OA\Schema(type: "string"))
        ],
        responses: [new OA\Response(response: 200, description: "OK")]
    )]
    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', Permission::class);
        
        $data = $this->permissionService->paginate($request->integer('per_page', 15));
        
        return $this->successResponse($data);
    }

    #[OA\Get(
        path: "/api/admin/permissions/{uuid}",
        summary: "Chi tiết Permission",
        security: [['bearerAuth' => []]],
        tags: ["Permissions"],
        parameters: [new OA\Parameter(name: "uuid", in: "path", required: true, schema: new OA\Schema(type: "string"))],
        responses: [new OA\Response(response: 200, description: "OK")]
    )]
    public function show(string $uuid): JsonResponse
    {
        $permission = $this->permissionService->findByUuidOrFail($uuid);
        $this->authorize('view', $permission);

        return $this->successResponse($permission);
    }

    #[OA\Post(
        path: "/api/admin/permissions",
        summary: "Tạo Permission",
        security: [['bearerAuth' => []]],
        tags: ["Permissions"],
        requestBody: new OA\RequestBody(content: new OA\JsonContent(properties: [
            new OA\Property(property: "name", type: "string", example: "product.view"),
            new OA\Property(property: "description", type: "string"),
            new OA\Property(property: "module", type: "string", example: "product")
        ])),
        responses: [new OA\Response(response: 201, description: "Created")]
    )]
    public function store(StorePermissionRequest $request): JsonResponse
    {
        $this->authorize('create', Permission::class);
        $data = $this->permissionService->create($request->validated());
        
        return $this->successResponse($data, 'Permission created successfully', 201);
    }

    #[OA\Put(
        path: "/api/admin/permissions/{uuid}",
        summary: "Cập nhật Permission",
        security: [['bearerAuth' => []]],
        tags: ["Permissions"],
        parameters: [new OA\Parameter(name: "uuid", in: "path", required: true)],
        responses: [new OA\Response(response: 200, description: "Updated")]
    )]
    public function update(UpdatePermissionRequest $request, string $uuid): JsonResponse
    {
        $this->authorize('update', Permission::class);
        $data = $this->permissionService->update($uuid, $request->validated());

        return $this->successResponse($data, 'Permission updated successfully');
    }

    #[OA\Delete(
        path: "/api/admin/permissions/{uuid}",
        summary: "Xóa Permission",
        security: [['bearerAuth' => []]],
        tags: ["Permissions"],
        parameters: [new OA\Parameter(name: "uuid", in: "path", required: true)],
        responses: [new OA\Response(response: 200, description: "Deleted")]
    )]
    public function destroy(string $uuid): JsonResponse
    {
        $this->authorize('delete', Permission::class);
        $this->permissionService->delete($uuid);

        return $this->successResponse(null, 'Permission deleted successfully');
    }

    #[OA\Get(
        path: "/api/admin/my-permissions",
        summary: "Lấy danh sách quyền của user đang login",
        security: [['bearerAuth' => []]],
        tags: ["Permissions"],
        responses: [new OA\Response(response: 200, description: "OK")]
    )]
    public function myPermissions(Request $request): JsonResponse
    {
        $permissions = $this->permissionService->getUserPermissions((int) $request->user()->id);
        return $this->successResponse($permissions);
    }
}