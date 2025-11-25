<?php

namespace Modules\Product\Domain\Repositories;

use Modules\Shared\Repositories\BaseRepositoryInterface;

interface ProductImageRepositoryInterface extends BaseRepositoryInterface
{
    public function unsetPrimary(int $productId): void;
}