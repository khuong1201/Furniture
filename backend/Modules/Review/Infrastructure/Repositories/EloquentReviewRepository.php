<?php

declare(strict_types=1);

namespace Modules\Review\Infrastructure\Repositories;

use Modules\Shared\Repositories\EloquentBaseRepository;
use Modules\Review\Domain\Repositories\ReviewRepositoryInterface;
use Modules\Review\Domain\Models\Review;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class EloquentReviewRepository extends EloquentBaseRepository implements ReviewRepositoryInterface
{
    public function __construct(Review $model)
    {
        parent::__construct($model);
    }

    public function filter(array $filters): LengthAwarePaginator
    {
        $query = $this->query()->with(['user:id,name,avatar_url']); 

        // 1. Lọc theo Product UUID 
        // (Nếu Admin không truyền product_uuid thì nó sẽ bỏ qua block này -> Lấy tất cả)
        if (!empty($filters['product_uuid'])) {
            $query->whereHas('product', function($q) use ($filters) {
                $q->where('uuid', $filters['product_uuid']);
            });
        }

        // 2. Lọc trạng thái duyệt (Admin lọc bài chưa duyệt is_approved=false)
        if (isset($filters['is_approved'])) {
            $query->where('is_approved', filter_var($filters['is_approved'], FILTER_VALIDATE_BOOLEAN));
        }
        
        // 3. Lọc rating
        if (!empty($filters['rating'])) {
            if (is_array($filters['rating'])) {
                $query->whereIn('rating', $filters['rating']);
            } else {
                $query->where('rating', $filters['rating']);
            }
        }

        // 4. Lọc ảnh
        if (!empty($filters['has_image']) && filter_var($filters['has_image'], FILTER_VALIDATE_BOOLEAN)) {
            $query->whereNotNull('images')
                  ->whereJsonLength('images', '>', 0);
        }

        // FIX LỖI PHÂN TRANG:
        // Luôn sort ID để tránh dữ liệu bị nhảy lung tung
        return $query->orderBy('created_at', 'desc')
                     ->orderBy('id', 'desc') 
                     ->paginate((int) ($filters['per_page'] ?? 10));
    }

    public function getStats(int $productId): array
    {
        $stats = $this->model
            ->where('product_id', $productId)
            ->where('is_approved', true)
            ->selectRaw('avg(rating) as avg_rating, count(*) as count_rating')
            ->first();

        return [
            'avg_rating' => round((float)($stats->avg_rating ?? 0), 1),
            'count_rating' => (int)($stats->count_rating ?? 0)
        ];
    }

    public function getRatingCounts(int $productId): array
    {
        $results = $this->model
            ->where('product_id', $productId)
            ->where('is_approved', true)
            ->select('rating', DB::raw('count(*) as count'))
            ->groupBy('rating')
            ->pluck('count', 'rating') 
            ->toArray();

        $formatted = [];
        for ($i = 5; $i >= 1; $i--) {
            $formatted[$i] = $results[$i] ?? 0;
        }

        return $formatted;
    }
}