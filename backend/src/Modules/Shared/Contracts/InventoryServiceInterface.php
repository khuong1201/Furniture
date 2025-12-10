<?php

declare(strict_types=1);

namespace Modules\Shared\Contracts;

interface InventoryServiceInterface
{
    public function syncStock(int $variantId, array $stockData): void;
    
    /**
     * Trừ kho khi đặt hàng. Trả về ID kho đã trừ.
     */
    public function allocate(int $variantId, int $quantity): int; 

    /**
     * Hoàn kho khi hủy đơn.
     */
    public function restore(int $variantId, int $quantity, int $warehouseId): void;

    public function getTotalStock(int $variantId): int;
}