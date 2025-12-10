<?php

declare(strict_types=1);

namespace Modules\Review\Listeners;

use Modules\Review\Events\ReviewApproved;
use Modules\Review\Domain\Repositories\ReviewRepositoryInterface;
use Modules\Product\Domain\Models\Product;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class RecalculateProductRating implements ShouldQueue
{
    use InteractsWithQueue;

    public function __construct(protected ReviewRepositoryInterface $reviewRepo) {}

    public function handle(ReviewApproved $event): void
    {
        $productId = $event->review->product_id;
        
        // Lấy stats mới từ repository
        $stats = $this->reviewRepo->getStats($productId);
        
        // Cập nhật Product
        Product::where('id', $productId)->update([
            'rating_avg' => $stats['avg_rating'],
            'rating_count' => $stats['count_rating']
        ]);
    }
}