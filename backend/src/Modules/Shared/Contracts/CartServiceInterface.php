<?php

declare(strict_types=1);

namespace Modules\Shared\Contracts;

interface CartServiceInterface
{
    /**
     * Lấy thông tin giỏ hàng (kèm calculate giá, check tồn kho realtime).
     */
    public function getMyCart(int|string $userId): array;

    /**
     * Xóa sạch giỏ hàng sau khi đặt hàng thành công.
     */
    public function clearCart(int|string $userId): void;
}