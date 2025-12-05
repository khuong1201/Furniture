<?php

declare(strict_types=1);

namespace Modules\Wishlist\Services;

use Modules\Shared\Services\BaseService;
use Modules\Wishlist\Domain\Repositories\WishlistRepositoryInterface;
use Modules\Product\Domain\Models\Product;
use Illuminate\Validation\ValidationException;
use Illuminate\Pagination\LengthAwarePaginator;

class WishlistService extends BaseService
{
    public function __construct(WishlistRepositoryInterface $repository)
    {
        parent::__construct($repository);
    }

    public function getMyWishlist(int $userId, int $perPage = 15): LengthAwarePaginator
    {
        return $this->repository->getByUser($userId, $perPage);
    }

    /**
     * Toggle wishlist status (Add if not exists, Remove if exists)
     * @return array{status: string, message: string}
     */
    public function toggle(int $userId, string $productUuid): array
    {
        $product = Product::where('uuid', $productUuid)->first();
        
        if (!$product) {
            throw ValidationException::withMessages(['product_uuid' => 'Product not found']);
        }

        $exists = $this->repository->findByUserAndProduct($userId, $product->id);

        if ($exists) {
            $exists->delete();
            return ['status' => 'removed', 'message' => 'Product removed from wishlist'];
        } 
        
        $this->repository->create([
            'user_id' => $userId,
            'product_id' => $product->id
        ]);
        
        return ['status' => 'added', 'message' => 'Product added to wishlist'];
    }
}