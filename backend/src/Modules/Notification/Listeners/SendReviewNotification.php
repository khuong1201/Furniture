<?php

declare(strict_types=1);

namespace Modules\Notification\Listeners;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;
use Modules\Notification\Services\NotificationService;
use Modules\Review\Events\ReviewPosted;
use Modules\User\Domain\Models\User;

class SendReviewNotification implements ShouldQueue
{
    use InteractsWithQueue;

    public $queue = 'notifications';

    public function __construct(
        protected NotificationService $notificationService
    ) {}

    public function handle(ReviewPosted $event): void
    {
        try {
            $review = $event->review;

            if (!$review->relationLoaded('product')) {
                $review->load('product');
            }

            $productName = $review->product->name ?? 'Product';

            // Find admins/moderators with review approval permission
            // Requires Spatie Permission
            $moderators = User::permission('review.approve')->get();

            foreach ($moderators as $admin) {
                $this->notificationService->send(
                    userId: $admin->id,
                    title: 'Review Pending Approval',
                    content: "Product [{$productName}] has received a new {$review->rating}-star review.",
                    type: 'info',
                    data: [
                        'review_uuid'  => $review->uuid,
                        'product_uuid' => $review->product->uuid,
                        'screen'       => 'admin_review_list',
                    ]
                );
            }
        } catch (\Throwable $e) {
            Log::error('ReviewNotification Error: ' . $e->getMessage());
        }
    }
}