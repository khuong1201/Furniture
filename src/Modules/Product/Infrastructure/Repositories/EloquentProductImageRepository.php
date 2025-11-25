<?php

namespace Modules\Product\Infrastructure\Repositories;

use Modules\Shared\Repositories\EloquentBaseRepository;
use Modules\Product\Domain\Models\ProductImage;
use Modules\Product\Domain\Repositories\ProductImageRepositoryInterface;

class EloquentProductImageRepository extends EloquentBaseRepository implements ProductImageRepositoryInterface
{
    public function __construct(ProductImage $model)
    {
        parent::__construct($model);
    }

    public function unsetPrimary(int $productId): void
    {
        $this->model->where('product_id', $productId)->update(['is_primary' => false]);
    }
}
