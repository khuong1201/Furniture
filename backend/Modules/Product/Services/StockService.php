<?php

namespace Modules\Product\Services;

use Modules\Product\Domain\Models\Product;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class StockService
{
    public function allocate(Product $product, int $qty)
    {
        $warehouse = $product->warehouses()
            ->wherePivot('quantity', '>=', $qty)
            ->orderByPivot('quantity', 'desc') 
            ->first();

        if (!$warehouse) {
            throw ValidationException::withMessages([
                'stock' => ["Sản phẩm {$product->name} không đủ hàng trong bất kỳ kho nào."]
            ]);
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
                 throw new \RuntimeException("Không tìm thấy kho để hoàn tồn cho sản phẩm {$product->name}");
            }
        }

        $product->warehouses()->updateExistingPivot($warehouseId, [
            'quantity' => DB::raw("quantity + {$qty}")
        ]);
    }
}