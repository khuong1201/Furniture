<?php

namespace Modules\Product\Services;

use Modules\Product\Domain\Models\Product;
use Illuminate\Support\Facades\DB;

class StockService
{
    public function allocate(Product $product, int $qty)
    {
        $warehouse = $product->warehouses()
            ->wherePivot('quantity', '>=', $qty)
            ->orderBy('pivot_quantity', 'desc')
            ->first();

        if (!$warehouse) {
            throw new \Exception("Không đủ hàng cho sản phẩm {$product->name}");
        }

        $product->warehouses()->updateExistingPivot($warehouse->id, [
            'quantity' => DB::raw("quantity - {$qty}")
        ]);

        return $warehouse;
    }

    public function restore(Product $product, int $qty, ?int $warehouseId = null)
    {
        if (!$warehouseId) {
            $warehouseId = $product->warehouses()->first()?->id;
            if (!$warehouseId) {
                throw new \Exception("Không tìm thấy kho để restore cho sản phẩm {$product->name}");
            }
        }

        $product->warehouses()->updateExistingPivot($warehouseId, [
            'quantity' => DB::raw("quantity + {$qty}")
        ]);
    }
}
