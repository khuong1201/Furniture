<?php

declare(strict_types=1);

namespace Modules\Wishlist\Services;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Modules\Product\Domain\Repositories\ProductRepositoryInterface;
use Modules\Shared\Exceptions\BusinessException;
use Modules\Shared\Services\BaseService;
use Modules\Wishlist\Domain\Repositories\WishlistRepositoryInterface;

class WishlistService extends BaseService
{
    public function __construct(
        WishlistRepositoryInterface $repository,
        protected ProductRepositoryInterface $productRepo
    ) {
        parent::__construct($repository);
    }

    public function getMyWishlist(int $userId, int $perPage = 15): LengthAwarePaginator
    {
        return $this->repository->getByUser($userId, $perPage);
    }

    public function toggle(int $userId, string $productUuid): array
    {
        $product = $this->productRepo->findByUuid($productUuid);
        
        if (!$product) {
            throw new BusinessException(404160); 
        }

        $exists = $this->repository->findByUserAndProduct($userId, $product->id);

        if ($exists) {
            $exists->delete();
            return ['status' => 'removed', 'message' => 'Đã xóa khỏi danh sách yêu thích'];
        } 
        
        $count = $this->repository->query()->where('user_id', $userId)->count();
        if ($count >= 50) {
            throw new BusinessException(400231, 'Danh sách yêu thích đã đầy (Max 50).');
        }

        $this->repository->create([
            'user_id'    => $userId,
            'product_id' => $product->id
        ]);
        
        return ['status' => 'added', 'message' => 'Đã thêm vào danh sách yêu thích'];
    }
}