<?php

namespace Modules\Warehouse\Http\Controllers;

use Modules\Shared\Http\Controllers\BaseController;
use Modules\Shared\Http\Resources\ApiResponse;
use Modules\Warehouse\Services\WarehouseService;
use Modules\Warehouse\Domain\Models\Warehouse; 
use Modules\Warehouse\Http\Requests\StoreWarehouseRequest;
use Modules\Warehouse\Http\Requests\UpdateWarehouseRequest;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use OpenApi\Attributes as OA;

#[OA\Tag(
    name: "Warehouses",
    description: "API quản lý Kho hàng (Admin Only)"
)]

class WarehouseController extends BaseController
{
    public function __construct(WarehouseService $service)
    {
        parent::__construct($service);
    }
    
    #[OA\Get(
        path: "/api/admin/warehouses",
        summary: "Xem danh sách Kho hàng (Admin)",
        security: [['bearerAuth' => []]],
        tags: ["Warehouses"],
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
        $this->authorize('viewAny', Warehouse::class);

        $data = $this->service->paginate($request->get('per_page', 15), $request->all());
        return response()->json(ApiResponse::paginated($data));
    }

    #[OA\Post(
        path: "/api/admin/warehouses",
        summary: "Tạo Kho hàng mới (Admin)",
        security: [['bearerAuth' => []]],
        tags: ["Warehouses"],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ["name", "code"],
                properties: [
                    new OA\Property(property: "name", type: "string", example: "Kho Trung Tâm Miền Bắc"),
                    new OA\Property(property: "code", type: "string", example: "NB01"),
                    new OA\Property(property: "address", type: "string", nullable: true),
                ]
            )
        ),
        responses: [
            new OA\Response(response: 201, description: "Warehouse created"),
            new OA\Response(response: 403, description: "Forbidden")
        ]
    )]

    public function store(Request $request): JsonResponse
    {
        $this->authorize('create', Warehouse::class);

        $validatedData = app(StoreWarehouseRequest::class)->validated();

        $data = $this->service->create($validatedData);
        
        return response()->json(ApiResponse::success($data, 'Warehouse created successfully', 201), 201);
    }

    #[OA\Get(
        path: "/api/admin/warehouses/{uuid}",
        summary: "Xem chi tiết Kho hàng (Admin)",
        security: [['bearerAuth' => []]],
        tags: ["Warehouses"],
        parameters: [
            new OA\Parameter(name: "uuid", in: "path", required: true, schema: new OA\Schema(type: "string", format: "uuid"))
        ],
        responses: [
            new OA\Response(response: 200, description: "Successful operation"),
            new OA\Response(response: 403, description: "Forbidden"),
            new OA\Response(response: 404, description: "Warehouse not found")
        ]
    )]

    public function show(string $uuid): JsonResponse
    {
        $warehouse = $this->service->getRepository()->findByUuid($uuid);
        if (!$warehouse) {
            return response()->json(ApiResponse::error('Warehouse not found', 404), 404);
        }

        $this->authorize('view', $warehouse);

        return response()->json(ApiResponse::success($warehouse));
    }

    #[OA\Put(
        path: "/api/admin/warehouses/{uuid}",
        summary: "Cập nhật Kho hàng (Admin)",
        security: [['bearerAuth' => []]],
        tags: ["Warehouses"],
        parameters: [
            new OA\Parameter(name: "uuid", in: "path", required: true, schema: new OA\Schema(type: "string", format: "uuid"))
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: "name", type: "string", example: "Kho Trung Tâm Miền Bắc (Update)"),
                    new OA\Property(property: "address", type: "string", nullable: true),
                ]
            )
        ),
        responses: [
            new OA\Response(response: 200, description: "Warehouse updated"),
            new OA\Response(response: 403, description: "Forbidden"),
            new OA\Response(response: 404, description: "Warehouse not found")
        ]
    )]

    public function update(Request $request, string $uuid): JsonResponse
    {
        $warehouse = $this->service->getRepository()->findByUuid($uuid);
        if (!$warehouse) {
            return response()->json(ApiResponse::error('Warehouse not found', 404), 404);
        }

        $this->authorize('update', $warehouse);

        $validatedData = app(UpdateWarehouseRequest::class)->validated();

        $data = $this->service->update($uuid, $validatedData);

        return response()->json(ApiResponse::success($data, 'Warehouse updated successfully'));
    }

    #[OA\Delete(
        path: "/api/admin/warehouses/{uuid}",
        summary: "Xóa Kho hàng (Admin)",
        security: [['bearerAuth' => []]],
        tags: ["Warehouses"],
        parameters: [
            new OA\Parameter(name: "uuid", in: "path", required: true, schema: new OA\Schema(type: "string", format: "uuid"))
        ],
        responses: [
            new OA\Response(response: 200, description: "Warehouse deleted"),
            new OA\Response(response: 403, description: "Forbidden"),
            new OA\Response(response: 409, description: "Conflict (Warehouse still has stock)"),
            new OA\Response(response: 404, description: "Warehouse not found")
        ]
    )]
    
    public function destroy(string $uuid): JsonResponse
    {
        $warehouse = $this->service->getRepository()->findByUuid($uuid);
        if (!$warehouse) {
            return response()->json(ApiResponse::error('Warehouse not found', 404), 404);
        }

        $this->authorize('delete', $warehouse);

        $this->service->delete($uuid);

        return response()->json(ApiResponse::success(null, 'Warehouse deleted successfully'));
    }
}