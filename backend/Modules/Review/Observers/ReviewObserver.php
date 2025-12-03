<?php

namespace Modules\Review\Observers;

use Modules\Review\Domain\Models\Review;
use Modules\Product\Domain\Models\Product;

class ReviewObserver
{
    public function created(Review $review): void
    {
        $this->recalculateProductRating($review->product_id);
    }

    public function updated(Review $review): void
    {
        if ($review->isDirty('rating')) {
            $this->recalculateProductRating($review->product_id);
        }
    }

    public function deleted(Review $review): void
    {
        $this->recalculateProductRating($review->product_id);
    }

    protected function recalculateProductRating(int $productId): void
    {
        $product = Product::find($productId);
        if (!$product) return;

        $stats = $product->reviews()
            ->selectRaw('avg(rating) as avg_rating, count(*) as count_rating')
            ->first();

        $product->updateQuietly([ 
            'rating_avg' => round($stats->avg_rating ?? 0, 2),
            'rating_count' => $stats->count_rating ?? 0
        ]);
    }
}