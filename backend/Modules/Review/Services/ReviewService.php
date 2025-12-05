<?php

declare(strict_types=1);

namespace Modules\Review\Services;

use Modules\Shared\Services\BaseService;
use Modules\Review\Domain\Repositories\ReviewRepositoryInterface;
use Modules\Product\Domain\Repositories\ProductRepositoryInterface;
use Illuminate\Support\Facades\Cache;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\ValidationException;
use Modules\Review\Events\ReviewPosted;
use Modules\Review\Events\ReviewApproved;

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
        if (!$product) {
            throw ValidationException::withMessages(['product_uuid' => 'Product not found']);
        }

        // Check unique: 1 user - 1 review - 1 product
        $exists = $this->repository->query()
            ->where('user_id', auth()->id())
            ->where('product_id', $product->id)
            ->exists();

        if ($exists) {
            throw ValidationException::withMessages(['product_uuid' => 'You have already reviewed this product.']);
        }

        $reviewData = [
            'product_id' => $product->id,
            'user_id' => auth()->id(),
            'rating' => $data['rating'],
            'comment' => $data['comment'] ?? null,
            'images' => $data['images'] ?? [],
            'is_approved' => false 
        ];

        $review = $this->repository->create($reviewData);
        
        // Clear cache stats
        $this->clearStatsCache($product->id);
        
        event(new ReviewPosted($review));

        return $review;
    }

    public function update(string $uuid, array $data): Model
    {
        $review = $this->repository->findByUuidOrFail($uuid);

        if (!auth()->user()->hasRole('admin')) {
            unset($data['is_approved']);
        }

        $oldStatus = $review->is_approved;
        
        $this->repository->update($review, $data);
        
        // Clear cache nếu rating thay đổi hoặc trạng thái duyệt thay đổi
        if (isset($data['rating']) || ($data['is_approved'] ?? false) !== $oldStatus) {
             $this->clearStatsCache($review->product_id);
        }
        
        if (!$oldStatus && ($data['is_approved'] ?? false)) {
             event(new ReviewApproved($review));
        }

        return $review;
    }
    
    public function delete(string $uuid): bool
    {
        $review = $this->repository->findByUuidOrFail($uuid);
        $productId = $review->product_id;
        
        $result = $this->repository->delete($review);
        
        if ($result) {
             $this->clearStatsCache($productId);
             // Trigger event để tính lại rating cho product
             event(new ReviewApproved($review)); 
        }
        
        return $result;
    }

    public function getReviewStats(string $productUuid): array
    {
        $cacheKey = "review_stats_{$productUuid}";

        return Cache::remember($cacheKey, 3600, function () use ($productUuid) {
            $product = $this->productRepo->findByUuid($productUuid);
            if (!$product) {
                return $this->getEmptyStats();
            }

            $counts = $this->repository->getRatingCounts($product->id);
            $totalReviews = array_sum($counts);
            
            $sumRating = 0;
            foreach ($counts as $star => $count) {
                $sumRating += ($star * $count);
            }
            
            $avgRating = $totalReviews > 0 ? round($sumRating / $totalReviews, 1) : 0;

            $distribution = [];
            // Loop từ 5 xuống 1 để UI hiển thị đúng thứ tự
            for ($i = 5; $i >= 1; $i--) {
                $count = $counts[$i] ?? 0;
                $percent = $totalReviews > 0 ? round(($count / $totalReviews) * 100, 1) : 0;
                
                $distribution[] = [
                    'star' => $i,
                    'count' => $count,
                    'percent' => $percent
                ];
            }

            return [
                'total_reviews' => $totalReviews,
                'average_rating' => $avgRating,
                'distribution' => $distribution
            ];
        });
    }

    private function getEmptyStats(): array
    {
        return [
            'total_reviews' => 0,
            'average_rating' => 0,
            'distribution' => array_map(fn($i) => ['star' => $i, 'count' => 0, 'percent' => 0], range(5, 1, -1))
        ];
    }

    public function clearStatsCache(int $productId): void
    {
        $product = $this->productRepo->findById($productId);
        if ($product) {
            Cache::forget("review_stats_{$product->uuid}");
        }
    }
}