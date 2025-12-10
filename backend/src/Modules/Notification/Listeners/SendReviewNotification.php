<?php

declare(strict_types=1);

namespace Modules\Notification\Listeners;

use Modules\Review\Events\ReviewPosted;
use Modules\Notification\Services\NotificationService;
use Modules\User\Domain\Models\User;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

class SendReviewNotification implements ShouldQueue
{
    use InteractsWithQueue;
    
    public $queue = 'notifications';

    public function __construct(protected NotificationService $notificationService) {}

    public function handle(ReviewPosted $event): void
    {
        try {
            $review = $event->review;
            
            if (!$review->relationLoaded('product')) {
                $review->load('product');
            }

            $productName = $review->product->name ?? 'Sản phẩm';
            
            // Tìm Admin/Moderator có quyền duyệt
            // Yêu cầu: Spatie Permission
            $moderators = User::permission('review.approve')->get();

            foreach ($moderators as $admin) {
                $this->notificationService->send(
                    userId: $admin->id,
                    title: 'Đánh giá chờ duyệt',
                    content: "SP [{$productName}] có đánh giá {$review->rating} sao mới.",
                    type: 'info',
                    data: [
                        'review_uuid' => $review->uuid,
                        'product_uuid' => $review->product->uuid,
                        'screen' => 'admin_review_list'
                    ]
                );
            }
        } catch (\Throwable $e) {
            Log::error("ReviewNotification Error: " . $e->getMessage());
        }
    }
}