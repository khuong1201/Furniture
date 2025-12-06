<?php

declare(strict_types=1);

namespace Modules\Notification\Listeners;

use Modules\Payment\Events\PaymentCompleted;
use Modules\Notification\Services\NotificationService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

class SendPaymentSuccessNotification implements ShouldQueue
{
    use InteractsWithQueue;

    /**
     * Inject NotificationService để dùng hàm send()
     */
    public function __construct(
        protected NotificationService $notificationService
    ) {}

    /**
     * Handle the event.
     */
    public function handle(PaymentCompleted $event): void
    {
        try {
            $payment = $event->payment;
            
            // Lấy User ID từ payment (giả sử model Payment có user_id)
            $userId = $payment->user_id; 

            // Format tiền tệ (nếu cần)
            $amount = number_format((float)$payment->amount, 0, ',', '.');

            // Nội dung thông báo
            $title = 'Thanh toán thành công';
            $content = "Giao dịch thanh toán #{$payment->uuid} với số tiền {$amount} VND đã hoàn tất.";
            
            // Metadata kèm theo (để frontend click vào xem chi tiết)
            $data = [
                'payment_uuid' => $payment->uuid,
                'order_id' => $payment->order_id ?? null, // Nếu có liên kết đơn hàng
                'amount' => $payment->amount
            ];

            // Gọi Service để lưu DB và bắn Pusher
            $this->notificationService->send(
                userId: $userId,
                title: $title,
                content: $content,
                type: 'payment_success', // Loại thông báo để frontend hiển thị icon phù hợp
                data: $data
            );

            Log::info("Đã gửi thông báo thanh toán cho User ID: {$userId}");

        } catch (\Exception $e) {
            Log::error("Lỗi gửi thông báo thanh toán: " . $e->getMessage());
        }
    }
}