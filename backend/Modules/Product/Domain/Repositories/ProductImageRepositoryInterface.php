<?php

namespace Modules\Product\Domain\Repositories;

use Modules\Shared\Repositories\BaseRepositoryInterface;
use Modules\Product\Domain\Models\ProductImage;

interface ProductImageRepositoryInterface extends BaseRepositoryInterface 
{
    public function unsetPrimary(int $productId): void;
}