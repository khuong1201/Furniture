<?php

declare(strict_types=1);

namespace Modules\Inventory\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Inventory\Domain\Models\InventoryStock;
use Modules\Inventory\Http\Requests\AdjustInventoryRequest;
use Modules\Inventory\Http\Requests\UpsertInventoryRequest;
use Modules\Inventory\Http\Resources\InventoryResource; 
use Modules\Inventory\Services\InventoryService;
use Modules\Shared\Http\Controllers\BaseController;
use OpenApi\Attributes as OA;

#[OA\Tag(name: "Inventory", description: "Inventory Management (Admin/Staff)")]
class InventoryController extends BaseController
{
    public function __construct(InventoryService $service)
    {
        parent::__construct($service);
    }

    #[OA\Get(
        path: "/api/admin/inventories",
        summary: "List Inventory Items",
        security: [['bearerAuth' => []]],
        tags: ["Inventory"],
        parameters: [
            new OA\Parameter(name: "warehouse_uuid", in: "query", schema: new OA\Schema(type: "string", format: "uuid")),
            new OA\Parameter(name: "search", in: "query", description: "Search Product Name or SKU", schema: new OA\Schema(type: "string")),
            new OA\Parameter(name: "page", in: "query", schema: new OA\Schema(type: "integer")),
            new OA\Parameter(name: "per_page", in: "query", schema: new OA\Schema(type: "integer")),
            new OA\Parameter(name: "status", in: "query", schema: new OA\Schema(type: "string", enum: ["out_of_stock", "low_stock", "in_stock"])),
            new OA\Parameter(name: "sort", in: "query", schema: new OA\Schema(type: "string", enum: ["latest", "qty_asc", "qty_desc"])),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: "Inventory List",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "success", type: "boolean", example: true),
                        new OA\Property(
                            property: "data",
                            type: "array",
                            items: new OA\Items(ref: "#/components/schemas/InventoryResource")
                        ),
                        new OA\Property(property: "meta", type: "object")
                    ]
                )
            )
        ]
    )]
    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', InventoryStock::class);
        $filters = $request->all();

        $paginator = $this->service->filter($filters);
        return $this->successResponse(InventoryResource::collection($paginator));
    }

    #[OA\Get(
        path: "/api/admin/inventories/{uuid}",
        summary: "Get Inventory Detail",
        security: [['bearerAuth' => []]],
        tags: ["Inventory"],
        parameters: [
            new OA\Parameter(name: "uuid", in: "path", required: true, schema: new OA\Schema(type: "string", format: "uuid"))
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: "Inventory Detail",
                content: new OA\JsonContent(properties: [
                    new OA\Property(property: "success", type: "boolean", example: true),
                    new OA\Property(property: "data", ref: "#/components/schemas/InventoryResource")
                ])
            ),
            new OA\Response(response: 404, description: "Not Found")
        ]
    )]
    public function show(string $uuid): JsonResponse
    {
        $this->authorize('viewAny', InventoryStock::class); 
        
        $inventory = $this->service->findByUuidOrFail($uuid);
        
        return $this->successResponse(new InventoryResource($inventory));
    }

    #[OA\Get(
        path: "/api/admin/inventories/dashboard-stats",
        summary: "Get Dashboard Overview Stats",
        description: "Returns cards for current status and movements for current month.",
        security: [['bearerAuth' => []]],
        tags: ["Inventory"],
        parameters: [
            new OA\Parameter(name: "warehouse_uuid", in: "query", schema: new OA\Schema(type: "string", format: "uuid")),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: "Dashboard Stats",
                content: new OA\JsonContent(properties: [
                    new OA\Property(property: "success", type: "boolean", example: true),
                    new OA\Property(property: "data", type: "object", properties: [
                        new OA\Property(property: "cards", type: "object", properties: [
                            new OA\Property(property: "total_skus", type: "integer", example: 20),
                            new OA\Property(property: "total_items", type: "integer", example: 4815),
                            new OA\Property(property: "out_of_stock_count", type: "integer", example: 0),
                            new OA\Property(property: "low_stock_count", type: "integer", example: 2),
                            new OA\Property(property: "old_stock_count", type: "integer", example: 5),
                        ]),
                        new OA\Property(property: "stock_movements", type: "object", properties: [
                            new OA\Property(property: "inbound", type: "integer", example: 150),
                            new OA\Property(property: "outbound", type: "integer", example: 9),
                            new OA\Property(property: "period", type: "string", example: "current_month"),
                        ])
                    ])
                ])
            )
        ]
    )]
    public function dashboardStats(Request $request): JsonResponse
    {
        $data = $this->service->getDashboardStats($request->input('warehouse_uuid'));
        return $this->successResponse($data);
    }

    #[OA\Get(
        path: "/api/admin/inventories/movements-chart",
        summary: "Get Movement History for Chart",
        security: [['bearerAuth' => []]],
        tags: ["Inventory"],
        parameters: [
            new OA\Parameter(name: "warehouse_uuid", in: "query", schema: new OA\Schema(type: "string", format: "uuid")),
            new OA\Parameter(name: "period", in: "query", description: "Default: week", schema: new OA\Schema(type: "string", enum: ["week", "month", "year"])),
            new OA\Parameter(name: "month", in: "query", description: "Required if period=month (1-12)", schema: new OA\Schema(type: "integer")),
            new OA\Parameter(name: "year", in: "query", description: "Required if period=month/year", schema: new OA\Schema(type: "integer")),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: "Success",
                content: new OA\JsonContent(properties: [
                    new OA\Property(property: "success", type: "boolean", example: true),
                    new OA\Property(property: "data", type: "array", items: new OA\Items(properties: [
                        new OA\Property(property: "name", type: "string", example: "25/12"),
                        new OA\Property(property: "inbound", type: "integer", example: 50),
                        new OA\Property(property: "outbound", type: "integer", example: 30),
                    ]))
                ])
            )
        ]
    )]
    public function movementsChart(Request $request): JsonResponse
    {
        $period = $request->input('period', 'week');
        $month = $request->has('month') ? (int)$request->input('month') : null;
        $year = $request->has('year') ? (int)$request->input('year') : null;

        $data = $this->service->getMovementChartData(
            $request->input('warehouse_uuid'),
            $period,
            $month,
            $year
        );
        
        return $this->successResponse($data);
    }
    #[OA\Post(
        path: "/api/admin/inventories/adjust",
        summary: "Adjust Inventory Stock (+/-)",
        security: [['bearerAuth' => []]],
        tags: ["Inventory"],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ["inventory_uuid", "quantity"],
                properties: [
                    new OA\Property(
                        property: "inventory_uuid",
                        type: "string",
                        format: "uuid",
                        description: "Inventory stock UUID"
                    ),
                    new OA\Property(
                        property: "quantity",
                        type: "integer",
                        description: "Positive or negative quantity"
                    ),
                    new OA\Property(
                        property: "reason",
                        type: "string",
                        example: "Manual adjustment"
                    ),
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: "Success",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "success", type: "boolean", example: true),
                        new OA\Property(property: "message", type: "string", example: "Inventory adjusted successfully"),
                        new OA\Property(property: "data", ref: "#/components/schemas/InventoryResource")
                    ]
                )
            ),
            new OA\Response(response: 403, description: "Forbidden"),
            new OA\Response(response: 422, description: "Validation Error")
        ]
    )]
    public function adjust(AdjustInventoryRequest $request): JsonResponse
    {
        if (!$request->user()->hasPermissionTo('inventory.adjust')) {
            return $this->errorResponse('Forbidden', 403023, 403);
        }
        $stock = $this->service->adjust(
            $request->input('inventory_uuid'),
            (int) $request->input('quantity'),
            $request->input('reason', 'manual')
        );
        return $this->successResponse(new InventoryResource($stock), 'Inventory adjusted successfully');
    }

    #[OA\Post(
        path: "/api/admin/inventories/upsert",
        summary: "Hard Set Inventory (Stocktake)",
        security: [['bearerAuth' => []]],
        tags: ["Inventory"],
        requestBody: new OA\RequestBody(content: new OA\JsonContent(properties: [
            new OA\Property(property: "variant_uuid", type: "string"),
            new OA\Property(property: "warehouse_uuid", type: "string"),
            new OA\Property(property: "quantity", type: "integer"),
            new OA\Property(property: "min_threshold", type: "integer"),
        ])),
        responses: [
            new OA\Response(response: 200, description: "Success", content: new OA\JsonContent(properties: [
                new OA\Property(property: "success", type: "boolean", example: true),
                new OA\Property(property: "data", ref: "#/components/schemas/InventoryResource")
            ]))
        ]
    )]
    public function upsert(UpsertInventoryRequest $request): JsonResponse
    {
        $this->authorize('create', InventoryStock::class);
        $stock = $this->service->upsert($request->validated());
        return $this->successResponse(new InventoryResource($stock), 'Inventory updated successfully');
    }
}