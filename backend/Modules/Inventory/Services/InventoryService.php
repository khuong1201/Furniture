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
            $this->productRepo->findById($data['product_id']) ?? throw ValidationException::withMessages(['product_id' => 'Invalid product']);
            $this->warehouseRepo->findById($data['warehouse_id']) ?? throw ValidationException::withMessages(['warehouse_id' => 'Invalid warehouse']);

            $inv = $this->repository->findByProductAndWarehouse($data['product_id'], $data['warehouse_id'], true);
            
            $quantity = $data['quantity'] ?? ($inv?->stock_quantity ?? 0);
            $minThreshold = $data['min_threshold'] ?? ($inv?->min_threshold ?? 0);
            
            $status = $this->calcStatus($quantity, $minThreshold);
            
            $payload = array_merge($data, ['status' => $status]);

            if ($inv) {
                return $this->repository->update($inv, $payload);
            }

            return $this->repository->create($payload);
        });
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