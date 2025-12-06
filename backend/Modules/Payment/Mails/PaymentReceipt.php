<?php

declare(strict_types=1);

namespace Modules\Payment\Mails;

use Modules\Payment\Domain\Models\Payment;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class PaymentReceipt extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(
        public Payment $payment
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Xác nhận Thanh toán Thành công | Mã giao dịch: ' . $this->payment->uuid,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'payment::emails.payment-receipt',
            with: [
                'payment' => $this->payment,
                'user' => $this->payment->user, 
            ],
        );
    }

    public function attachments(): array
    {
        return [];
    }
}