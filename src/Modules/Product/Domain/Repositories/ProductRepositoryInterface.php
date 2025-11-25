<?php

namespace Modules\Product\Domain\Repositories;

use Modules\Shared\Repositories\BaseRepositoryInterface;

interface ProductRepositoryInterface extends BaseRepositoryInterface
{
    // Thêm phương thức filter nếu cần
    public function filter(array $filters);
}

