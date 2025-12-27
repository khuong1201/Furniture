<?php

declare(strict_types=1);

namespace Modules\Shared\Contracts;

interface InventoryServiceInterface
{
    /**
     * Đồng bộ kho từ warehouse (External System).
     */
    public function syncStock(int $variantId, array $stockData): void;

    /**
     * Kiểm tra và giữ hàng (Reserve stock) khi tạo đơn.
     * @throws BusinessException nếu hết hàng.
     * @return int Transaction ID hoặc Inventory ID đã trừ.
     */
    public function allocate(int $variantId, int $quantity): int;

    /**
     * Hoàn kho khi hủy đơn hàng.
     */
    public function restore(int $variantId, int $quantity, int $warehouseId): void;

    /**
     * Lấy tổng tồn kho hiện tại.
     */
    public function getTotalStock(int $variantId): int;
}