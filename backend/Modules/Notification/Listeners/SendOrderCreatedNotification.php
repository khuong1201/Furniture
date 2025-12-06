<?php

declare(strict_types=1);

namespace Modules\Notification\Listeners;

use Modules\Order\Events\OrderCreated;
use Modules\Notification\Services\NotificationService;
use Modules\Shared\Services\MailService;
use Modules\Order\Mails\OrderConfirmationMail;
use Illuminate\Contracts\Queue\ShouldQueue;

class SendOrderCreatedNotification implements ShouldQueue
{
    public $queue = 'notifications';

    public function __construct(
        protected NotificationService $notificationService,
        protected MailService $mailService
    ) {}

    public function handle(OrderCreated $event): void
    {
        $order = $event->order;
        $order->load('user');
        $user = $order->user;

        // 1. Gửi Notification In-App (Code cũ)
        if ($user) {
            $this->notificationService->send(
                $user->id,
                'Đặt hàng thành công',
                "Đơn hàng #{$order->uuid} đã được tạo.",
                'success',
                ['order_uuid' => $order->uuid]
            );

            // 2. Gửi Email & Ghi Log Nghiệp vụ
            if ($user->email) {
                // Gọi Service gửi mail (Kỹ thuật)
                $isQueued = $this->mailService->send($user, new OrderConfirmationMail($order));

                // Nếu queue thành công -> Ghi log nghiệp vụ vào DB (Audit Log)
                if ($isQueued && method_exists($order, 'logActivity')) {
                    $order->logActivity(
                        event: 'system_notification',
                        description: "Đã gửi email xác nhận đơn hàng tới {$user->email}",
                        properties: ['type' => 'email', 'template' => 'order_confirmation']
                    );
                }
            }
        }
    }
}