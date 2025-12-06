<?php

declare(strict_types=1);

namespace Modules\Promotion\Domain\Traits;

use Modules\Promotion\Domain\Models\Promotion;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Builder;

trait HasPromotions
{
    // ... (Các hàm relation và accessor cũ giữ nguyên)

    /**
     * Scope: Lọc các sản phẩm ĐANG có Flash Sale hợp lệ.
     * Dùng để query SQL trực tiếp: Product::hasActiveFlashSale()->get()
     */
    public function scopeHasActiveFlashSale(Builder $query): Builder
    {
        return $query->whereHas('promotions', function ($q) {
            $now = now();
            // Query vào bảng promotions thông qua pivot
            $q->where('is_active', true)
              ->where('start_date', '<=', $now)
              ->where('end_date', '>=', $now)
              // Kiểm tra thêm quantity nếu cần thiết (optional)
              ->where(function($subQ) {
                  $subQ->where('quantity', '=', 0) // Không giới hạn
                       ->orWhereColumn('used_count', '<', 'quantity'); // Hoặc chưa hết mã
              });
        });
    }
}