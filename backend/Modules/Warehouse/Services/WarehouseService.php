<?php

namespace Modules\Warehouse\Services;

use Modules\Shared\Services\BaseService;
use Modules\Warehouse\Domain\Repositories\WarehouseRepositoryInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\ValidationException;

class WarehouseService extends BaseService
{
    public function __construct(WarehouseRepositoryInterface $repository)
    {
        parent::__construct($repository);
    }

    protected function beforeDelete(Model $model): void
    {
        $hasStock = \Modules\Inventory\Models\Inventory::where('warehouse_id', $model->id)
            ->where('stock_quantity', '>', 0)
            ->exists();

        if ($hasStock) {
            throw ValidationException::withMessages([
                'warehouse' => ['Cannot delete warehouse containing stock. Please transfer or clear stock first.']
            ]);
        }
    }
}