<?php

namespace Modules\Product\Services;

use Modules\Product\Domain\Models\ProductVariant;
use Modules\Product\Domain\Models\InventoryStock;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class StockService
{
    public function allocate(string $variantUuid, int $qty)
    {
        $variant = ProductVariant::where('uuid', $variantUuid)->firstOrFail();

        $stock = InventoryStock::where('product_variant_id', $variant->id)
            ->where('quantity', '>=', $qty)
            ->orderBy('quantity', 'desc')
            ->first();

        if (!$stock) {
            throw ValidationException::withMessages([
                'stock' => ["Sản phẩm (SKU: {$variant->sku}) không đủ hàng trong bất kỳ kho nào."]
            ]);
        }

        $stock->decrement('quantity', $qty);

        return $stock; 
    }

    public function restore(string $variantUuid, int $qty, int $warehouseId)
    {
        $variant = ProductVariant::where('uuid', $variantUuid)->firstOrFail();

        $stock = InventoryStock::where('product_variant_id', $variant->id)
            ->where('warehouse_id', $warehouseId)
            ->first();

        if ($stock) {
            $stock->increment('quantity', $qty);
        } else {
            throw new \RuntimeException("Không tìm thấy kho (ID: $warehouseId) để hoàn tồn cho SKU {$variant->sku}");
        }
    }
}