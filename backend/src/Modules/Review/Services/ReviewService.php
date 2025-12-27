<?php

declare(strict_types=1);

namespace Modules\Review\Services;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Modules\Product\Domain\Repositories\ProductRepositoryInterface;
use Modules\Review\Domain\Models\Review;
use Modules\Review\Domain\Repositories\ReviewRepositoryInterface;
use Modules\Review\Events\ReviewApproved;
use Modules\Review\Events\ReviewPosted;
use Modules\Shared\Exceptions\BusinessException;
use Modules\Shared\Services\BaseService;

class ReviewService extends BaseService
{
    public function __construct(
        ReviewRepositoryInterface $repository, 
        protected ProductRepositoryInterface $productRepo
    ) {
        parent::__construct($repository);
    }

    public function listReviewsForProduct(string $productUuid, array $filters): LengthAwarePaginator
    {
        $filters['product_uuid'] = $productUuid;

        return $this->repository->filter($filters);
    }

    public function paginate(int $perPage = 15, array $filters = []): LengthAwarePaginator
    {
        $filters['per_page'] = $perPage;
        return $this->repository->filter($filters);
    }

    public function create(array $data): Model
    {
        $userId = auth()->id();
        $product = $this->productRepo->findByUuid($data['product_uuid']);

        if (!$product) {
            throw new BusinessException(404160); 
        }

        $exists = $this->repository->query()
            ->where('user_id', $userId)
            ->where('product_id', $product->id)
            ->exists();

        if ($exists) {
            throw new BusinessException(409181, 'Bạn đã đánh giá sản phẩm này rồi.');
        }

        $orderItem = \Modules\Order\Domain\Models\OrderItem::whereHas('order', function ($q) use ($userId) {
                $q->where('user_id', $userId)
                  ->where('status', 'delivered'); 
            })
            ->whereHas('variant', fn($q) => $q->where('product_id', $product->id))
            ->latest()
            ->first();

        $review = $this->repository->create([
            'product_id'  => $product->id,
            'user_id'     => $userId,
            'order_id'    => $orderItem?->order_id, 
            'rating'      => $data['rating'],
            'comment'     => $data['comment'] ?? null,
            'images'      => $data['images'] ?? [],
            'is_approved' => false 
        ]);

        event(new ReviewPosted($review));

        return $review;
    }

    public function update(string $uuid, array $data): Model
    {
        $review = $this->findByUuidOrFail($uuid);
        
        if (!auth()->user()->hasRole('admin')) {
            unset($data['is_approved']);
        }

        $oldStatus = $review->is_approved;
        $oldRating = $review->rating;
        
        $this->repository->update($review, $data);

        if ($oldRating != $data['rating'] || ($data['is_approved'] ?? $oldStatus) !== $oldStatus) {
             $this->recalculateStats($review->product_id);
        }

        if (!$oldStatus && ($data['is_approved'] ?? false)) {
             event(new ReviewApproved($review));
        }

        return $review;
    }
    
    public function delete(string $uuid): bool
    {
        $review = $this->findByUuidOrFail($uuid);
        $productId = $review->product_id;
        
        $result = parent::delete($uuid);
        
        if ($result) {
             $this->recalculateStats($productId);
        }
        
        return $result;
    }

    public function recalculateStats(int $productId): void
    {
        $product = $this->productRepo->findById($productId);
        if ($product) {
            Cache::forget("review_stats_{$product->uuid}");
        }

        $stats = $this->repository->getStats($productId);
        
        $this->productRepo->update($product, [
            'rating_avg' => $stats['avg_rating'],
            'rating_count' => $stats['count_rating']
        ]);
    }

    public function getReviewStats(string $productUuid): array
    {
        $cacheKey = "review_stats_{$productUuid}";

        return Cache::remember($cacheKey, 3600, function () use ($productUuid) {
            $product = $this->productRepo->findByUuid($productUuid);
            if (!$product) return $this->getEmptyStats();

            $counts = $this->repository->getRatingCounts($product->id);
            $totalReviews = array_sum($counts);
            
            $sumRating = 0;
            foreach ($counts as $star => $count) {
                $sumRating += ($star * $count);
            }
            
            $avgRating = $totalReviews > 0 ? round($sumRating / $totalReviews, 1) : 0;

            $distribution = [];
            for ($i = 5; $i >= 1; $i--) {
                $count = $counts[$i] ?? 0;
                $percent = $totalReviews > 0 ? round(($count / $totalReviews) * 100) : 0;
                
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
        $distribution = [];
        for ($i = 5; $i >= 1; $i--) {
            $distribution[] = ['star' => $i, 'count' => 0, 'percent' => 0];
        }
        return [
            'total_reviews' => 0,
            'average_rating' => 0,
            'distribution' => $distribution
        ];
    }
}