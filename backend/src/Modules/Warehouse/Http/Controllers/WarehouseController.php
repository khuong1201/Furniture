<?php

declare(strict_types=1);

namespace Modules\Warehouse\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Modules\Shared\Http\Controllers\BaseController;
use Modules\Warehouse\Services\WarehouseService;
use Modules\Warehouse\Domain\Models\Warehouse; 
use Modules\Warehouse\Http\Requests\StoreWarehouseRequest;
use Modules\Warehouse\Http\Requests\UpdateWarehouseRequest;
use OpenApi\Attributes as OA;

#[OA\Tag(
    name: "Warehouses", 
    description: "Warehouse Management API (Admin Only)"
)]
class WarehouseController extends BaseController
{
    public function __construct(WarehouseService $service)
    {
        parent::__construct($service);
    }
    
    #[OA\Get(
        path: "/api/admin/warehouses/{uuid}/stats",
        summary: "View Warehouse Statistics (Dashboard)",
        description: "Returns SKU count, total items, low stock, and old stock counts.",
        security: [['bearerAuth' => []]],
        tags: ["Warehouses"],
        parameters: [ 
            new OA\Parameter(name: "uuid", in: "path", required: true, schema: new OA\Schema(type: "string", format: "uuid")) 
        ],
        responses: [ 
            new OA\Response(
                response: 200, 
                description: "Success",
                content: new OA\JsonContent(properties: [
                    new OA\Property(property: "success", type: "boolean", example: true),
                    new OA\Property(property: "data", type: "object", 
                        properties: [
                            new OA\Property(property: "total_skus", type: "integer", example: 150),
                            new OA\Property(property: "total_items", type: "integer", example: 5000),
                            new OA\Property(property: "low_stock_count", type: "integer", example: 5),
                            new OA\Property(property: "old_stock_count", type: "integer", example: 12)
                        ]
                    )
                ])
            ),
            new OA\Response(response: 404, description: "Warehouse not found")
        ]
    )]
    public function stats(string $uuid): JsonResponse
    {
        $this->service->findByUuidOrFail($uuid);
        $data = $this->service->getWarehouseStats($uuid);
        return $this->successResponse($data);
    }
    
    #[OA\Get(
        path: "/api/admin/warehouses",
        summary: "List Warehouses",
        description: "Paginated list of warehouses with search and filters.",
        security: [['bearerAuth' => []]],
        tags: ["Warehouses"],
        parameters: [
            new OA\Parameter(name: "page", in: "query", description: "Page number", schema: new OA\Schema(type: "integer", default: 1)),
            new OA\Parameter(name: "per_page", in: "query", description: "Items per page", schema: new OA\Schema(type: "integer", default: 15)),
            new OA\Parameter(name: "search", in: "query", description: "Search by name or location", schema: new OA\Schema(type: "string")),
            new OA\Parameter(name: "manager_id", in: "query", description: "Filter by Manager ID", schema: new OA\Schema(type: "integer")),
            new OA\Parameter(name: "is_active", in: "query", description: "Filter status (1: Active, 0: Inactive)", schema: new OA\Schema(type: "boolean")),
        ],
        responses: [
            new OA\Response(
                response: 200, 
                description: "Success",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "success", type: "boolean", example: true),
                        new OA\Property(
                            property: "data", 
                            type: "array", 
                            items: new OA\Items(
                                properties: [
                                    new OA\Property(property: "id", type: "integer", example: 1),
                                    new OA\Property(property: "uuid", type: "string", format: "uuid"),
                                    new OA\Property(property: "name", type: "string", example: "Hanoi Warehouse"),
                                    new OA\Property(property: "location", type: "string", example: "Cau Giay, Hanoi"),
                                    new OA\Property(property: "is_active", type: "boolean", example: true),
                                    new OA\Property(property: "manager", type: "object", description: "Manager Info")
                                ]
                            )
                        ),
                        new OA\Property(property: "meta", type: "object")
                    ]
                )
            )
        ]
    )]
    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', Warehouse::class);
        $data = $this->service->paginate($request->integer('per_page', 15), $request->all());
        return $this->successResponse($data);
    }

    #[OA\Post(
        path: "/api/admin/warehouses",
        summary: "Create Warehouse",
        security: [['bearerAuth' => []]],
        tags: ["Warehouses"],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ["name"],
                properties: [
                    new OA\Property(property: "name", type: "string", example: "Hanoi Warehouse"),
                    new OA\Property(property: "location", type: "string", example: "Cau Giay, Hanoi"),
                    new OA\Property(property: "manager_id", type: "integer", description: "Manager User ID"),
                    new OA\Property(property: "is_active", type: "boolean", default: true),
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 201, 
                description: "Created Successfully",
                content: new OA\JsonContent(properties: [
                    new OA\Property(property: "success", type: "boolean", example: true),
                    new OA\Property(property: "data", type: "object"),
                    new OA\Property(property: "message", type: "string", example: "Warehouse created successfully")
                ])
            ),
            new OA\Response(
                response: 409, 
                description: "Business Error (Duplicate Name)",
                content: new OA\JsonContent(properties: [
                    new OA\Property(property: "success", type: "boolean", example: false),
                    new OA\Property(property: "error_code", type: "integer", example: 409221),
                    new OA\Property(property: "message", type: "string")
                ])
            ),
            new OA\Response(response: 422, description: "Validation Error")
        ]
    )]
    public function store(StoreWarehouseRequest $request): JsonResponse
    {
        $this->authorize('create', Warehouse::class);
        $data = $this->service->create($request->validated());
        return $this->successResponse($data, 'Warehouse created successfully', 201);
    }

    #[OA\Get(
        path: "/api/admin/warehouses/{uuid}",
        summary: "Get Warehouse Details",
        security: [['bearerAuth' => []]],
        tags: ["Warehouses"],
        parameters: [ 
            new OA\Parameter(name: "uuid", in: "path", required: true, schema: new OA\Schema(type: "string", format: "uuid")) 
        ],
        responses: [ 
            new OA\Response(
                response: 200, 
                description: "Success",
                content: new OA\JsonContent(properties: [
                    new OA\Property(property: "success", type: "boolean", example: true),
                    new OA\Property(property: "data", type: "object")
                ])
            ),
            new OA\Response(response: 404, description: "Not Found (Code: 404220)")
        ]
    )]
    public function show(string $uuid): JsonResponse
    {
        $warehouse = $this->service->findByUuidOrFail($uuid);
        $this->authorize('view', $warehouse);
        $warehouse->load('manager');

        return $this->successResponse($warehouse);
    }

    #[OA\Put(
        path: "/api/admin/warehouses/{uuid}",
        summary: "Update Warehouse",
        security: [['bearerAuth' => []]],
        tags: ["Warehouses"],
        parameters: [ 
            new OA\Parameter(name: "uuid", in: "path", required: true, schema: new OA\Schema(type: "string", format: "uuid")) 
        ],
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
        responses: [ 
            new OA\Response(
                response: 200, 
                description: "Updated Successfully",
                content: new OA\JsonContent(properties: [
                    new OA\Property(property: "success", type: "boolean", example: true),
                    new OA\Property(property: "message", type: "string", example: "Warehouse updated successfully")
                ])
            ),
            new OA\Response(response: 409, description: "Duplicate Name (Code: 409221)")
        ]
    )]
    public function update(UpdateWarehouseRequest $request, string $uuid): JsonResponse
    {
        $warehouse = $this->service->findByUuidOrFail($uuid);
        $this->authorize('update', $warehouse);

        $data = $this->service->update($uuid, $request->validated());

        return $this->successResponse($data, 'Warehouse updated successfully');
    }

    #[OA\Delete(
        path: "/api/admin/warehouses/{uuid}",
        summary: "Delete Warehouse",
        description: "Note: Cannot delete warehouse if it still contains stock (Quantity > 0).",
        security: [['bearerAuth' => []]],
        tags: ["Warehouses"],
        parameters: [ 
            new OA\Parameter(name: "uuid", in: "path", required: true, schema: new OA\Schema(type: "string", format: "uuid")) 
        ],
        responses: [ 
            new OA\Response(
                response: 200, 
                description: "Deleted Successfully",
                content: new OA\JsonContent(properties: [
                    new OA\Property(property: "success", type: "boolean", example: true),
                    new OA\Property(property: "message", type: "string", example: "Warehouse deleted successfully")
                ])
            ),
            new OA\Response(
                response: 409, 
                description: "Cannot delete due to existing stock",
                content: new OA\JsonContent(properties: [
                    new OA\Property(property: "success", type: "boolean", example: false),
                    new OA\Property(property: "error_code", type: "integer", example: 409222),
                    new OA\Property(property: "message", type: "string", example: "Warehouse contains inventory items")
                ])
            )
        ]
    )]
    public function destroy(string $uuid): JsonResponse
    {
        $warehouse = $this->service->findByUuidOrFail($uuid);
        $this->authorize('delete', $warehouse);

        $this->service->delete($uuid);

        return $this->successResponse(null, 'Warehouse deleted successfully');
    }
}