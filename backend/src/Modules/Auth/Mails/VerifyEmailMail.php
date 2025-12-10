<?php

declare(strict_types=1);

namespace Modules\Auth\Mails;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Modules\User\Domain\Models\User;

class VerifyEmailMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public User $user,
        public string $otp
    ) {}

    public function build()
    {
        return $this->subject('Mã xác thực tài khoản (OTP)')
                    ->view('auth::emails.verify_email');
    }
}