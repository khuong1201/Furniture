<?php

namespace Modules\Inventory\Http\Controllers;

use Modules\Shared\Http\Controllers\BaseController;
use Modules\Inventory\Services\InventoryService;
use Modules\Inventory\Http\Requests\UpsertInventoryRequest;
use Modules\Inventory\Http\Requests\AdjustStockRequest;

class InventoryController extends BaseController
{
    public function __construct(InventoryService $service)
    {
        parent::__construct($service);
    }

    public function upsert(UpsertInventoryRequest $request)
    {
        $data = $request->validated();

        $inv = $this->service->upsert(
            $data['product_id'],
            $data['warehouse_id'],
            $data['quantity'],
            $data['min_threshold'] ?? 0,
            $data['max_threshold'] ?? null
        );

        return response()->json($inv, 201);
    }

    public function adjust($productId, $warehouseId, AdjustStockRequest $request)
    {
        $delta = $request->validated()['delta'];
        return response()->json($this->service->adjustStock($productId, $warehouseId, $delta));
    }
}
