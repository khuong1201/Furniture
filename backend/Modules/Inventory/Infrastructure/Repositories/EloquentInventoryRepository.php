<?php

declare(strict_types=1);

namespace Modules\Inventory\Infrastructure\Repositories;

use Modules\Shared\Repositories\EloquentBaseRepository;
use Modules\Inventory\Domain\Repositories\InventoryRepositoryInterface;
use Modules\Inventory\Domain\Models\InventoryStock;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;

class EloquentInventoryRepository extends EloquentBaseRepository implements InventoryRepositoryInterface
{
    public function __construct(InventoryStock $model)
    {
        parent::__construct($model);
    }

    public function findByVariantAndWarehouse(int $variantId, int $warehouseId, bool $lock = false): ?InventoryStock
    {
        $query = $this->model
            ->where('product_variant_id', $variantId)
            ->where('warehouse_id', $warehouseId);

        if ($lock) {
            $query->lockForUpdate();
        }

        return $query->first();
    }

    public function filter(array $filters): LengthAwarePaginator
    {
        $query = $this->query()->with([
            'variant.product', 
            'variant.attributeValues.attribute', 
            'warehouse'
        ]);

        if (!empty($filters['warehouse_uuid'])) {
            $query->whereHas('warehouse', fn($q) => $q->where('uuid', $filters['warehouse_uuid']));
        }

        if (!empty($filters['search'])) {
            $q = $filters['search'];
            $query->whereHas('variant', function (Builder $v) use ($q) {
                $v->where('sku', 'like', "%{$q}%")
                  ->orWhereHas('product', fn($p) => $p->where('name', 'like', "%{$q}%"));
            });
        }

        return $query->latest()->paginate($filters['per_page'] ?? 20);
    }
}