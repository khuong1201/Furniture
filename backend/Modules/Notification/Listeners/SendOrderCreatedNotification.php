<?php

declare(strict_types=1);

namespace Modules\Notification\Listeners;

use Modules\Order\Events\OrderCreated;
use Modules\Notification\Services\NotificationService;
use Modules\User\Domain\Models\User;
use Illuminate\Contracts\Queue\ShouldQueue;
use Modules\Shared\Services\MailService;
use Modules\Order\Mails\OrderConfirmationMail;
use Illuminate\Support\Facades\Log;

class SendOrderCreatedNotification implements ShouldQueue
{
    public $queue = 'default';

    public function __construct(
        protected NotificationService $notificationService,
        protected MailService $mailService
    ) {}

    public function handle(OrderCreated $event): void
    {
        $order = $event->order;
        // Fix logic: Eager load user để tránh query lặp và lỗi null
        $order->load('user');
        
        $user = $order->user;

        // 1. Gửi thông báo in-app cho Khách hàng
        if ($user) {
            $this->notificationService->send(
                $user->id,
                'Đặt hàng thành công',
                "Đơn hàng #{$order->uuid} của bạn đã được khởi tạo. Tổng tiền: " . number_format($order->total_amount) . "đ",
                'success',
                ['order_uuid' => $order->uuid, 'type' => 'order_detail']
            );

            // 2. Gửi Email xác nhận (Sử dụng MailService của Shared Module)
            if ($user->email) {
                $this->mailService->send($user, new OrderConfirmationMail($order));
            }
        }

        // 3. Gửi thông báo cho Admin/Manager
        // Logic: Tìm user có role admin. 
        // Lưu ý: Nếu hệ thống lớn, nên cache danh sách admin ID để tránh query DB liên tục.
        $admins = User::whereHas('roles', fn($q) => $q->where('name', 'admin'))->get();
        
        foreach ($admins as $admin) {
            $this->notificationService->send(
                $admin->id,
                'Đơn hàng mới',
                "Khách hàng " . ($user->name ?? 'Guest') . " vừa đặt đơn hàng mới #{$order->uuid}.",
                'info',
                ['order_uuid' => $order->uuid, 'type' => 'admin_order_detail']
            );
        }
    }
}