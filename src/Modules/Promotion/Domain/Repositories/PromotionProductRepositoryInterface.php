<?php

namespace Modules\Promotion\Domain\Repositories;

use Modules\Promotion\Domain\Models\PromotionProduct;

interface PromotionProductRepositoryInterface
{
    public function attachProducts(int $promotionId, array $productIds): void;
    public function detachProducts(int $promotionId, array $productIds): void;
}
