<?php

declare(strict_types=1);

namespace Modules\Permission\Http\Controllers;

use Modules\Shared\Http\Controllers\BaseController;
use Modules\Shared\Http\Resources\ApiResponse;
use Modules\Permission\Services\PermissionService;
use Modules\Permission\Http\Requests\StorePermissionRequest;
use Modules\Permission\Domain\Models\Permission;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use OpenApi\Attributes as OA;

#[OA\Tag(name: "Permissions", description: "Quản lý quyền hạn")]
class PermissionController extends BaseController
{
    protected PermissionService $permissionService;

    public function __construct(PermissionService $service)
    {
        parent::__construct($service);
        $this->permissionService = $service;
    }

    #[OA\Get(
        path: "/admin/permissions",
        summary: "Danh sách Permissions",
        security: [['bearerAuth' => []]],
        tags: ["Permissions"],
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
        $this->authorize('viewAny', Permission::class);
        return parent::index($request); 
    }
    
    #[OA\Get(
        path: "/admin/my-permissions",
        summary: "Quyền của tôi",
        security: [['bearerAuth' => []]],
        tags: ["Permissions"],
        responses: [
            new OA\Response(
                response: 200, 
                description: "Success",
                content: new OA\JsonContent(properties: [
                    new OA\Property(property: "success", type: "boolean", example: true),
                    new OA\Property(property: "data", type: "array", items: new OA\Items(type: "string"))
                ])
            )
        ]
    )]
    public function myPermissions(Request $request): JsonResponse
    {
        $permissions = $this->permissionService->getUserPermissions((int) $request->user()->id);
        
        return response()->json(ApiResponse::success($permissions));
    }

    #[OA\Post(
        path: "/admin/permissions",
        summary: "Tạo Permission",
        security: [['bearerAuth' => []]],
        tags: ["Permissions"],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ["name"],
                properties: [
                    new OA\Property(property: "name", type: "string", example: "product.view"),
                    new OA\Property(property: "description", type: "string"),
                    new OA\Property(property: "module", type: "string")
                ]
            )
        ),
        responses: [
            new OA\Response(response: 201, description: "Created"),
            new OA\Response(response: 422, description: "Validation Error")
        ]
    )]
    public function store(StorePermissionRequest $request): JsonResponse
    {
        $this->authorize('create', Permission::class);

        $data = $this->permissionService->create($request->validated());

        return response()->json(ApiResponse::success($data, 'Permission created successfully', 201), 201);
    }
}