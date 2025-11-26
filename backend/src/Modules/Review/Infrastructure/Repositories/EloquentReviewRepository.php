<?php

namespace Modules\Review\Infrastructure\Repositories;

use Modules\Review\Domain\Repositories\ReviewRepositoryInterface;
use Modules\Review\Domain\Models\Review;
use Illuminate\Pagination\LengthAwarePaginator;

class EloquentReviewRepository implements ReviewRepositoryInterface
{
    public function paginate(int $perPage = 10, array $filters = []): LengthAwarePaginator
    {
        $query = Review::query()->with(['user', 'product']);
        if (isset($filters['product_id'])) {
            $query->where('product_id', $filters['product_id']);
        }
        return $query->latest()->paginate($perPage);
    }

    public function create(array $data): Review
    {
        return Review::create($data);
    }

    public function update(Review $review, array $data): Review
    {
        $review->update($data);
        return $review;
    }

    public function delete(Review $review): bool
    {
        return $review->delete();
    }

    public function findByUuid(string $uuid): ?Review
    {
        return Review::where('uuid', $uuid)->first();
    }
}
