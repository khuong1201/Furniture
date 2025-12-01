<?php

namespace Modules\Inventory\Infrastructure\Repositories;

use Modules\Shared\Repositories\EloquentBaseRepository;
use Modules\Inventory\Domain\Models\Inventory;
use Modules\Inventory\Domain\Repositories\InventoryRepositoryInterface;

class EloquentInventoryRepository extends EloquentBaseRepository implements InventoryRepositoryInterface
{
    public function __construct(Inventory $model)
    {
        parent::__construct($model);
    }
    
    public function findByProductAndWarehouse(int $productId, int $warehouseId, bool $lock = false): ?Inventory
    {
        $query = $this->model
            ->where('product_id', $productId)
            ->where('warehouse_id', $warehouseId);
            
        if ($lock) {
            // Pessimistic Locking: SELECT ... FOR UPDATE
            // Chặn các transaction khác sửa dòng này cho đến khi transaction hiện tại commit
            $query->lockForUpdate();
        }
        
        return $query->first();
    }
    
    public function filter(array $filters): \Illuminate\Contracts\Pagination\LengthAwarePaginator
    {
        $query = $this->query()->with(['product', 'warehouse']);

        if (!empty($filters['product_id'])) {
            $query->where('product_id', $filters['product_id']);
        }
        if (!empty($filters['warehouse_id'])) {
            $query->where('warehouse_id', $filters['warehouse_id']);
        }
        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        return $query->latest()->paginate($filters['per_page'] ?? 15);
    }
}