<?php

namespace Modules\Inventory\Services;

use Modules\Shared\Services\BaseService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
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

    public function list(array $filters = [])
    {
        return $this->repository->paginate($filters['per_page'] ?? 15);
    }

    public function upsert(
        int $productId,
        int $warehouseId,
        int $quantity,
        int $minThreshold = 0,
        ?int $maxThreshold = null
    ) {
        return DB::transaction(function () use (
            $productId,
            $warehouseId,
            $quantity,
            $minThreshold,
            $maxThreshold
        ) {
            $product = $this->productRepo->findById($productId);
            $warehouse = $this->warehouseRepo->findById($warehouseId);

            if (! $product || ! $warehouse) {
                throw new \InvalidArgumentException('Product or Warehouse not found');
            }

            $inv = $this->repository->findByProductAndWarehouse($productId, $warehouseId);
            $status = $this->calcStatus($quantity, $minThreshold);

            if ($inv) {
                return $this->repository->update($inv, [
                    'stock_quantity' => $quantity,
                    'min_threshold' => $minThreshold,
                    'max_threshold' => $maxThreshold,
                    'status' => $status,
                ]);
            }

            return $this->repository->create([
                'uuid' => Str::uuid()->toString(),
                'product_id' => $productId,
                'warehouse_id' => $warehouseId,
                'stock_quantity' => $quantity,
                'min_threshold' => $minThreshold,
                'max_threshold' => $maxThreshold,
                'status' => $status,
            ]);
        });
    }

    public function adjustStock(int $productId, int $warehouseId, int $delta)
    {
        return DB::transaction(function () use ($productId, $warehouseId, $delta) {
            $inv = $this->repository->findByProductAndWarehouse($productId, $warehouseId);

            if (! $inv) {
                throw new \InvalidArgumentException('Inventory not found');
            }

            $newQty = max(0, $inv->stock_quantity + $delta);
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
