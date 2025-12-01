<?php

namespace Modules\Review\Services;

use Modules\Shared\Services\BaseService;
use Modules\Review\Domain\Repositories\ReviewRepositoryInterface;
use Modules\Product\Domain\Repositories\ProductRepositoryInterface;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;
class ReviewService extends BaseService
{
    public function __construct(
        ReviewRepositoryInterface $repository,
        protected ProductRepositoryInterface $productRepo
    ) {
        parent::__construct($repository);
    }

    public function create(array $data): Model
    {
        $product = $this->productRepo->findByUuid($data['product_uuid']);
        if (!$product) throw ValidationException::withMessages(['product_uuid' => 'Product not found']);

        $data['product_id'] = $product->id;
        $data['user_id'] = auth()->id();
        
        $data['is_approved'] = false; 

        try {
            return $this->repository->create($data);
        } catch (\Exception $e) {
            if (str_contains($e->getMessage(), 'Duplicate entry')) {
                throw ValidationException::withMessages(['product_uuid' => 'You have already reviewed this product.']);
            }
            throw $e;
        }
    }

    public function update(string $uuid, array $data): Model
    {
        $review = $this->repository->findByUuid($uuid);
        if (!$review) throw ValidationException::withMessages(['uuid' => 'Review not found']);

        if ($review->user_id !== auth()->id() && !auth()->user()->hasRole('admin')) {
             throw ValidationException::withMessages(['uuid' => 'Unauthorized']);
        }

        if (!auth()->user()->hasRole('admin')) {
            unset($data['is_approved']);
            $data['is_approved'] = false;
        }

        return $this->repository->update($review, $data);
    }
    
    public function delete(string $uuid): bool
    {
        $review = $this->repository->findByUuid($uuid);
        if (!$review) return false;
        
        if ($review->user_id !== auth()->id() && !auth()->user()->hasRole('admin')) {
             return false;
        }
        
        return $this->repository->delete($review);
    }
}