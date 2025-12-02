<?php

namespace Modules\Inventory\Services;

use Modules\Shared\Services\BaseService;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Modules\Inventory\Domain\Repositories\InventoryRepositoryInterface;
use Modules\Product\Domain\Repositories\ProductRepositoryInterface;
use Modules\Warehouse\Domain\Repositories\WarehouseRepositoryInterface; 

class InventoryService extends BaseService
{
    public function __construct(
        InventoryRepositoryInterface $repository,
        protected ProductRepositoryInterface $productRepo,
        protected WarehouseRepositoryInterface $warehouseRepo
    ) {
        parent::__construct($repository);
    }

    public function upsert(array $data)
    {
        return DB::transaction(function () use ($data) {
            $productId = $data['product_id'] ?? null;
            $warehouseId = $data['warehouse_id'] ?? null;

            if (isset($data['product_uuid'])) {
                $product = $this->productRepo->findByUuid($data['product_uuid']);
                if (!$product) throw ValidationException::withMessages(['product_uuid' => 'Invalid product']);
                $productId = $product->id;
            }

            if (isset($data['warehouse_uuid'])) {
                $warehouse = $this->warehouseRepo->findByUuid($data['warehouse_uuid']);
                if (!$warehouse) throw ValidationException::withMessages(['warehouse_uuid' => 'Invalid warehouse']);
                $warehouseId = $warehouse->id;
            }

            if (!$productId || !$warehouseId) {
                throw ValidationException::withMessages(['inventory' => 'Product and Warehouse identification required']);
            }

            $inv = $this->repository->findByProductAndWarehouse($productId, $warehouseId, true);
            
            $quantity = $data['quantity'] ?? ($inv?->stock_quantity ?? 0);
            $minThreshold = $data['min_threshold'] ?? ($inv?->min_threshold ?? 0);
            
            $status = $this->calcStatus($quantity, $minThreshold);
            
            $payload = [
                'product_id' => $productId,
                'warehouse_id' => $warehouseId,
                'stock_quantity' => $quantity,
                'min_threshold' => $minThreshold,
                'status' => $status
            ];

            if ($inv) {
                return $this->repository->update($inv, $payload);
            }

            return $this->repository->create($payload);
        });
    }

    public function adjustStockByUuid(string $productUuid, string $warehouseUuid, int $delta)
    {
        $product = $this->productRepo->findByUuid($productUuid);
        if (!$product) throw ValidationException::withMessages(['product_uuid' => 'Product not found']);

        $warehouse = $this->warehouseRepo->findByUuid($warehouseUuid);
        if (!$warehouse) throw ValidationException::withMessages(['warehouse_uuid' => 'Warehouse not found']);

        return $this->adjustStock($product->id, $warehouse->id, $delta);
    }

    public function adjustStock(int $productId, int $warehouseId, int $delta)
    {
        return DB::transaction(function () use ($productId, $warehouseId, $delta) {
            $inv = $this->repository->findByProductAndWarehouse($productId, $warehouseId, lock: true);

            if (!$inv) {
                if ($delta < 0) throw ValidationException::withMessages(['stock' => 'Inventory not found for deduction']);
                
                return $this->upsert([
                    'product_id' => $productId,
                    'warehouse_id' => $warehouseId,
                    'quantity' => $delta
                ]);
            }

            $newQty = $inv->stock_quantity + $delta;

            if ($newQty < 0) {
                throw ValidationException::withMessages(['stock' => "Insufficient stock. Current: {$inv->stock_quantity}, Requested deduction: " . abs($delta)]);
            }

            $status = $this->calcStatus($newQty, $inv->min_threshold);

            return $this->repository->update($inv, [
                'stock_quantity' => $newQty,
                'status' => $status,
            ]);
        });
    }

    protected function calcStatus(int $quantity, int $minThreshold): string
    {
        if ($quantity <= 0) return 'out_of_stock';
        if ($quantity <= $minThreshold) return 'low_stock';
        return 'in_stock';
    }
}