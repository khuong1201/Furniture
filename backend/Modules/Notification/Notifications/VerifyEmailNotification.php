<?php

declare(strict_types=1);

namespace Modules\Notification\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;
// Fix Namespace: Model User phải trỏ đúng Domain
use Modules\User\Domain\Models\User;

class VerifyEmailNotification extends Notification
{
    use Queueable;

    public function __construct(public string $otp) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Verify Your Account - OTP')
            ->greeting('Hello ' . $notifiable->name . '!')
            ->line('Your verification code is: ' . $this->otp)
            ->line('This code will expire in 10 minutes.')
            ->action('Verify Now', url('/verify')) 
            ->line('Thank you for using our application!');
    }
}