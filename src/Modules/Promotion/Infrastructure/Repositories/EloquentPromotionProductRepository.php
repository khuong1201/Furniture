<?php

namespace Modules\Promotion\Infrastructure\Repositories;

use Modules\Promotion\Domain\Repositories\PromotionProductRepositoryInterface;
use Modules\Promotion\Domain\Models\PromotionProduct;

class EloquentPromotionProductRepository implements PromotionProductRepositoryInterface
{
    public function attachProducts(int $promotionId, array $productIds): void
    {
        foreach ($productIds as $productId) {
            PromotionProduct::firstOrCreate([
                'promotion_id' => $promotionId,
                'product_id' => $productId,
            ]);
        }
    }

    public function detachProducts(int $promotionId, array $productIds): void
    {
        PromotionProduct::where('promotion_id', $promotionId)
            ->whereIn('product_id', $productIds)
            ->delete();
    }
}
