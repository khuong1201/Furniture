<?php

declare(strict_types=1);

namespace Modules\Inventory\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Modules\Shared\Http\Controllers\BaseController;
use Modules\Shared\Http\Resources\ApiResponse;
use Modules\Inventory\Services\InventoryService;
use Modules\Inventory\Http\Requests\AdjustInventoryRequest;
use Modules\Inventory\Http\Requests\UpsertInventoryRequest;
use Modules\Inventory\Domain\Models\InventoryStock;
use OpenApi\Attributes as OA;

#[OA\Tag(name: "Inventory", description: "Quản lý Tồn kho (Admin/Staff)")]
class InventoryController extends BaseController
{
    public function __construct(InventoryService $service)
    {
        parent::__construct($service);
    }

    #[OA\Get(
        path: "/admin/inventories",
        summary: "Xem danh sách tồn kho",
        security: [['bearerAuth' => []]],
        tags: ["Inventory"],
        parameters: [
            new OA\Parameter(name: "warehouse_uuid", in: "query", schema: new OA\Schema(type: "string", format: "uuid")),
            new OA\Parameter(name: "search", in: "query", description: "Tìm tên SP hoặc SKU", schema: new OA\Schema(type: "string")),
            new OA\Parameter(name: "page", in: "query", schema: new OA\Schema(type: "integer")),
        ],
        responses: [ new OA\Response(response: 200, description: "Success") ]
    )]
    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', InventoryStock::class);

        $data = $this->service->paginate($request->integer('per_page', 20), $request->all());
        return response()->json(ApiResponse::paginated($data));
    }

    #[OA\Post(
        path: "/admin/inventories/adjust",
        summary: "Điều chỉnh kho (Nhập/Xuất)",
        description: "Dùng để nhập hàng (+qty) hoặc xuất hủy (-qty).",
        security: [['bearerAuth' => []]],
        tags: ["Inventory"],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ["variant_uuid", "warehouse_uuid", "quantity"],
                properties: [
                    new OA\Property(property: "variant_uuid", type: "string", format: "uuid"),
                    new OA\Property(property: "warehouse_uuid", type: "string", format: "uuid"),
                    new OA\Property(property: "quantity", type: "integer", description: "Số dương để nhập, số âm để xuất"),
                    new OA\Property(property: "reason", type: "string", example: "Nhập hàng mới"),
                ]
            )
        ),
        responses: [ new OA\Response(response: 200, description: "Adjusted") ]
    )]
    public function adjust(AdjustInventoryRequest $request): JsonResponse
    {
        // Quyền riêng cho việc điều chỉnh kho (thường là thủ kho)
        if (!$request->user()->hasPermissionTo('inventory.adjust')) {
             return response()->json(ApiResponse::error('Forbidden', 403), 403);
        }

        $stock = $this->service->adjust(
            $request->input('variant_uuid'),
            $request->input('warehouse_uuid'),
            (int)$request->input('quantity'),
            $request->input('reason', 'manual')
        );

        return response()->json(ApiResponse::success($stock, 'Inventory adjusted successfully'));
    }

    #[OA\Post(
        path: "/admin/inventories/upsert",
        summary: "Set cứng tồn kho (Kiểm kê)",
        security: [['bearerAuth' => []]],
        tags: ["Inventory"],
        requestBody: new OA\RequestBody(
            content: new OA\JsonContent(
                required: ["variant_uuid", "warehouse_uuid", "quantity"],
                properties: [
                    new OA\Property(property: "variant_uuid", type: "string"),
                    new OA\Property(property: "warehouse_uuid", type: "string"),
                    new OA\Property(property: "quantity", type: "integer", description: "Số lượng thực tế"),
                    new OA\Property(property: "min_threshold", type: "integer"),
                ]
            )
        ),
        responses: [ new OA\Response(response: 200, description: "Updated") ]
    )]
    public function upsert(UpsertInventoryRequest $request): JsonResponse
    {
        $this->authorize('create', InventoryStock::class); 

        $stock = $this->service->upsert($request->validated());
        
        return response()->json(ApiResponse::success($stock, 'Inventory updated successfully'));
    }
}