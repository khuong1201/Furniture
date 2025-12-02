<?php

namespace Modules\Inventory\Http\Controllers;

use Modules\Shared\Http\Controllers\BaseController;
use Modules\Shared\Http\Resources\ApiResponse;
use Modules\Inventory\Services\InventoryService;
use Modules\Inventory\Domain\Models\Inventory;
use Modules\Inventory\Http\Requests\UpsertInventoryRequest;
use Modules\Inventory\Http\Requests\AdjustStockRequest;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use OpenApi\Attributes as OA;

#[OA\Tag(
    name: "Inventory",
    description: "API quản lý Tồn kho (Admin Only)"
)]

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
            new OA\Parameter(name: "page", in: "query", schema: new OA\Schema(type: "integer")),
            new OA\Parameter(name: "per_page", in: "query", schema: new OA\Schema(type: "integer")),
            new OA\Parameter(name: "product_uuid", in: "query", description: "Filter theo UUID sản phẩm", required: false, schema: new OA\Schema(type: "string", format: "uuid")),
            new OA\Parameter(name: "warehouse_uuid", in: "query", description: "Filter theo UUID kho", required: false, schema: new OA\Schema(type: "string", format: "uuid")),
        ],
        responses: [
            new OA\Response(response: 200, description: "Successful operation"),
            new OA\Response(response: 403, description: "Forbidden (Not Admin)")
        ]
    )]

    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', Inventory::class);

        $data = $this->service->paginate($request->get('per_page', 15), $request->all());
        return response()->json(ApiResponse::paginated($data));
    }

     #[OA\Post(
        path: "/admin/inventories/upsert",
        summary: "Thêm hoặc Cập nhật Tồn kho (Upsert)",
        description: "Tạo record tồn kho mới hoặc ghi đè (overwrite) số lượng nếu đã tồn tại.",
        security: [['bearerAuth' => []]],
        tags: ["Inventory"],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ["product_uuid", "warehouse_uuid", "quantity"],
                properties: [
                    new OA\Property(property: "product_uuid", type: "string", format: "uuid"),
                    new OA\Property(property: "warehouse_uuid", type: "string", format: "uuid"),
                    new OA\Property(property: "quantity", type: "integer", description: "Số lượng tồn kho mới (overwrite)"),
                    new OA\Property(property: "min_threshold", type: "integer", description: "Ngưỡng cảnh báo thấp nhất", nullable: true),
                ]
            )
        ),
        responses: [
            new OA\Response(response: 200, description: "Inventory updated successfully"),
            new OA\Response(response: 403, description: "Forbidden")
        ]
    )]

    public function upsert(Request $request): JsonResponse
    {
        $this->authorize('create', Inventory::class);

        $validatedRequest = app(UpsertInventoryRequest::class);

        $data = $this->service->upsert($validatedRequest->validated());
        
        return response()->json(ApiResponse::success($data, 'Inventory updated successfully'));
    }

    #[OA\Patch(
        path: "/admin/inventories/{productUuid}/{warehouseUuid}/adjust",
        summary: "Điều chỉnh số lượng tồn kho (Tăng/Giảm Delta)",
        description: "Sử dụng để nhập kho (delta > 0) hoặc trừ kho thủ công (delta < 0).",
        security: [['bearerAuth' => []]],
        tags: ["Inventory"],
        parameters: [
            new OA\Parameter(name: "productUuid", in: "path", required: true, schema: new OA\Schema(type: "string", format: "uuid")),
            new OA\Parameter(name: "warehouseUuid", in: "path", required: true, schema: new OA\Schema(type: "string", format: "uuid")),
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ["delta"],
                properties: [
                    new OA\Property(property: "delta", type: "integer", description: "Giá trị thay đổi (có thể âm hoặc dương)")
                ]
            )
        ),
        responses: [
            new OA\Response(response: 200, description: "Stock adjusted successfully"),
            new OA\Response(response: 403, description: "Forbidden"),
            new OA\Response(response: 422, description: "Insufficient stock or Validation error")
        ]
    )]
    
    public function adjust(Request $request, string $productUuid, string $warehouseUuid): JsonResponse
    {
        $this->authorize('update', Inventory::class); 

        $validatedRequest = app(AdjustStockRequest::class);

        $data = $this->service->adjustStockByUuid(
            $productUuid, 
            $warehouseUuid, 
            $validatedRequest->validated()['delta']
        );
        
        return response()->json(ApiResponse::success($data, 'Stock adjusted successfully'));
    }
}