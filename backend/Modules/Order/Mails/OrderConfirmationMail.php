<?php

declare(strict_types=1);

namespace Modules\Order\Mails;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Modules\Order\Domain\Models\Order;

class OrderConfirmationMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Order $order)
    {
        $this->order->load(['user', 'items']); 
    }

    public function build()
    {
        return $this->subject('Xác nhận đơn hàng #' . $this->order->uuid)
                    ->view('order::emails.order_confirmation'); // Namespace 'order'
    }
}