<?php

declare(strict_types=1);

namespace Modules\Warehouse\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Modules\Shared\Http\Controllers\BaseController;
use Modules\Shared\Http\Resources\ApiResponse;
use Modules\Warehouse\Services\WarehouseService;
use Modules\Warehouse\Domain\Models\Warehouse; 
use Modules\Warehouse\Http\Requests\StoreWarehouseRequest;
use Modules\Warehouse\Http\Requests\UpdateWarehouseRequest;
use OpenApi\Attributes as OA;

#[OA\Tag(name: "Warehouses", description: "API quản lý Kho hàng (Admin Only)")]
class WarehouseController extends BaseController
{
    public function __construct(WarehouseService $service)
    {
        parent::__construct($service);
    }
    
    #[OA\Get(
        path: "/admin/warehouses",
        summary: "Xem danh sách Kho hàng",
        security: [['bearerAuth' => []]],
        tags: ["Warehouses"],
        parameters: [
            new OA\Parameter(name: "page", in: "query", schema: new OA\Schema(type: "integer")),
            new OA\Parameter(name: "search", in: "query", schema: new OA\Schema(type: "string")),
            new OA\Parameter(name: "manager_id", in: "query", schema: new OA\Schema(type: "integer")),
        ],
        responses: [ new OA\Response(response: 200, description: "Success") ]
    )]
    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', Warehouse::class);

        $data = $this->service->paginate($request->integer('per_page', 15), $request->all());
        return response()->json(ApiResponse::paginated($data));
    }

    #[OA\Post(
        path: "/admin/warehouses",
        summary: "Tạo Kho hàng mới",
        security: [['bearerAuth' => []]],
        tags: ["Warehouses"],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ["name"],
                properties: [
                    new OA\Property(property: "name", type: "string", example: "Kho Hồ Chí Minh"),
                    new OA\Property(property: "location", type: "string", example: "Quận 7, TP.HCM"),
                    new OA\Property(property: "manager_id", type: "integer", example: 1),
                    new OA\Property(property: "is_active", type: "boolean", default: true),
                ]
            )
        ),
        responses: [ new OA\Response(response: 201, description: "Created") ]
    )]
    public function store(StoreWarehouseRequest $request): JsonResponse
    {
        $this->authorize('create', Warehouse::class);

        $data = $this->service->create($request->validated());
        
        return response()->json(ApiResponse::success($data, 'Warehouse created successfully', 201), 201);
    }

    #[OA\Get(
        path: "/admin/warehouses/{uuid}",
        summary: "Xem chi tiết Kho hàng",
        security: [['bearerAuth' => []]],
        tags: ["Warehouses"],
        parameters: [ new OA\Parameter(name: "uuid", in: "path", required: true, schema: new OA\Schema(type: "string", format: "uuid")) ],
        responses: [ new OA\Response(response: 200, description: "Success") ]
    )]
    public function show(string $uuid): JsonResponse
    {
        $warehouse = $this->service->findByUuidOrFail($uuid);
        $this->authorize('view', $warehouse);
        
        // Eager load manager info
        $warehouse->load('manager');

        return response()->json(ApiResponse::success($warehouse));
    }

    #[OA\Put(
        path: "/admin/warehouses/{uuid}",
        summary: "Cập nhật Kho hàng",
        security: [['bearerAuth' => []]],
        tags: ["Warehouses"],
        parameters: [ new OA\Parameter(name: "uuid", in: "path", required: true, schema: new OA\Schema(type: "string", format: "uuid")) ],
        requestBody: new OA\RequestBody(
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: "name", type: "string"),
                    new OA\Property(property: "location", type: "string"),
                    new OA\Property(property: "manager_id", type: "integer"),
                    new OA\Property(property: "is_active", type: "boolean"),
                ]
            )
        ),
        responses: [ new OA\Response(response: 200, description: "Updated") ]
    )]
    public function update(UpdateWarehouseRequest $request, string $uuid): JsonResponse
    {
        $warehouse = $this->service->findByUuidOrFail($uuid);
        $this->authorize('update', $warehouse);

        $data = $this->service->update($uuid, $request->validated());

        return response()->json(ApiResponse::success($data, 'Warehouse updated successfully'));
    }

    #[OA\Delete(
        path: "/admin/warehouses/{uuid}",
        summary: "Xóa Kho hàng",
        security: [['bearerAuth' => []]],
        tags: ["Warehouses"],
        parameters: [ new OA\Parameter(name: "uuid", in: "path", required: true, schema: new OA\Schema(type: "string", format: "uuid")) ],
        responses: [ new OA\Response(response: 200, description: "Deleted") ]
    )]
    public function destroy(string $uuid): JsonResponse
    {
        $warehouse = $this->service->findByUuidOrFail($uuid);
        $this->authorize('delete', $warehouse);

        $this->service->delete($uuid);

        return response()->json(ApiResponse::success(null, 'Warehouse deleted successfully'));
    }
}