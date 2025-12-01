<?php

namespace Modules\Inventory\Http\Controllers;

use Modules\Shared\Http\Controllers\BaseController;
use Modules\Shared\Http\Resources\ApiResponse;
use Modules\Inventory\Services\InventoryService;
use Modules\Inventory\Http\Requests\UpsertInventoryRequest;
use Modules\Inventory\Http\Requests\AdjustStockRequest;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
class InventoryController extends BaseController
{
    public function __construct(InventoryService $service)
    {
        parent::__construct($service);
    }

    public function index(Request $request): JsonResponse
    {
        $data = $this->service->paginate($request->get('per_page', 15), $request->all());
        return response()->json(ApiResponse::paginated($data));
    }

    public function upsert(UpsertInventoryRequest $request): JsonResponse
    {
        $data = $this->service->upsert($request->validated());
        return response()->json(ApiResponse::success($data, 'Inventory updated successfully'));
    }

    public function adjust(AdjustStockRequest $request, int $productId, int $warehouseId): JsonResponse
    {
        $data = $this->service->adjustStock(
            $productId, 
            $warehouseId, 
            $request->validated()['delta']
        );
        return response()->json(ApiResponse::success($data, 'Stock adjusted successfully'));
    }
}