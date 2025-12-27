<?php

declare(strict_types=1);

namespace Modules\Wishlist\Domain\Repositories;

use Modules\Shared\Contracts\BaseRepositoryInterface;
use Modules\Wishlist\Domain\Models\Wishlist;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface WishlistRepositoryInterface extends BaseRepositoryInterface
{
    public function getByUser(int $userId, int $perPage): LengthAwarePaginator;
    public function findByUserAndProduct(int $userId, int $productId): ?Wishlist;
}