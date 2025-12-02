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
        path: "/admin/warehouses",
        summary: "Xem danh sách Kho hàng (Admin)",
        security: [['bearerAuth' => []]],
        tags: ["Warehouses"],
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
        $this->authorize('viewAny', Warehouse::class);

        $data = $this->service->paginate($request->get('per_page', 15), $request->all());
        return response()->json(ApiResponse::paginated($data));
    }

    #[OA\Post(
        path: "/admin/warehouses",
        summary: "Tạo Kho hàng mới (Admin)",
        security: [['bearerAuth' => []]],
        tags: ["Warehouses"],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ["name", "code"],
                properties: [
                    new OA\Property(property: "name", type: "string", example: "Kho Trung Tâm"),
                    new OA\Property(property: "code", type: "string", example: "NB01"),
                    new OA\Property(property: "address", type: "string"),
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
        parameters: [
            new OA\Parameter(name: "uuid", in: "path", required: true, schema: new OA\Schema(type: "string", format: "uuid"))
        ],
        responses: [
            new OA\Response(response: 200, description: "Success"),
            new OA\Response(response: 404, description: "Not found")
        ]
    )]

    public function show(string $uuid): JsonResponse
    {
        $warehouse = $this->service->findByUuidOrFail($uuid);

        $this->authorize('view', $warehouse);

        return response()->json(ApiResponse::success($warehouse));
    }

    #[OA\Put(
        path: "/admin/warehouses/{uuid}",
        summary: "Cập nhật Kho hàng",
        security: [['bearerAuth' => []]],
        tags: ["Warehouses"],
        parameters: [
            new OA\Parameter(name: "uuid", in: "path", required: true, schema: new OA\Schema(type: "string", format: "uuid"))
        ],
        requestBody: new OA\RequestBody(
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: "name", type: "string"),
                    new OA\Property(property: "address", type: "string"),
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
        parameters: [
            new OA\Parameter(name: "uuid", in: "path", required: true, schema: new OA\Schema(type: "string", format: "uuid"))
        ],
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