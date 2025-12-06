<?php

declare(strict_types=1);

namespace Modules\Payment\Listeners;

use Modules\Payment\Events\PaymentCompleted;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Mail;
use Modules\Payment\Mail\PaymentReceipt; // Giả định Mailable này tồn tại
use Illuminate\Support\Facades\Log;

class SendPaymentReceiptEmail implements ShouldQueue
{
    /**
     * Listener này nên chạy nền (Queue) để không làm chậm response của API
     */
    public function __construct() {}

    /**
     * Xử lý sự kiện PaymentCompleted để gửi email.
     */
    public function handle(PaymentCompleted $event): void
    {
        $payment = $event->payment;
        
        // Cần đảm bảo model Payment đã load quan hệ User (hoặc có user_id)
        // Giả sử có quan hệ $payment->user
        $user = $payment->user; 

        if (!$user || !$user->email) {
            Log::warning("Không thể gửi email xác nhận thanh toán cho Payment {$payment->uuid}: Không tìm thấy User hoặc email.");
            return;
        }

        try {
            // [QUAN TRỌNG]: Gửi email hóa đơn. Cần Queue email nếu có thể.
            // send() sẽ gửi ngay lập tức, dùng queue() để chạy nền.
            Mail::to($user->email)->queue(new PaymentReceipt($payment)); 
            
            Log::info("Đã queue email xác nhận thanh toán cho user: {$user->email} | Payment: {$payment->uuid}");
            
        } catch (\Exception $e) {
            Log::error("Gửi email xác nhận thanh toán thất bại cho Payment {$payment->uuid}: " . $e->getMessage());
        }
    }
}