<?php

declare(strict_types=1);

namespace Modules\Wishlist\Infrastructure\Repositories;

use Modules\Shared\Repositories\EloquentBaseRepository;
use Modules\Wishlist\Domain\Repositories\WishlistRepositoryInterface;
use Modules\Wishlist\Domain\Models\Wishlist;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class EloquentWishlistRepository extends EloquentBaseRepository implements WishlistRepositoryInterface
{
    public function __construct(Wishlist $model)
    {
        parent::__construct($model);
    }

    public function getByUser(int $userId, int $perPage): LengthAwarePaginator
    {
        return $this->model
            ->with([
                'product.images' => function($q) {
                    $q->where('is_primary', true);
                },
                'product.category:id,name,slug' 
            ])
            ->where('user_id', $userId)
            ->latest()
            ->paginate($perPage);
    }

    public function findByUserAndProduct(int $userId, int $productId): ?Wishlist
    {
        return $this->model
            ->where('user_id', $userId)
            ->where('product_id', $productId)
            ->first();
    }
}