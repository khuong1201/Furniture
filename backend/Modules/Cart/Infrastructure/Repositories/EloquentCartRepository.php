<?php

namespace Modules\Cart\Infrastructure\Repositories;

use Modules\Shared\Repositories\EloquentBaseRepository;
use Modules\Cart\Domain\Models\Cart;
use Modules\Cart\Domain\Repositories\CartRepositoryInterface;

class EloquentCartRepository extends EloquentBaseRepository implements CartRepositoryInterface
{
    public function __construct(Cart $model)
    {
        parent::__construct($model);
    }

    public function findByUser(int $userId): ?Cart
    {
        return $this->model->with(['items.product.images', 'items.product.promotions'])
                    ->where('user_id', $userId)
                    ->first();
    }

    public function firstOrCreateByUser(int $userId): Cart
    {
        return $this->model->firstOrCreate(['user_id' => $userId]);
    }
}