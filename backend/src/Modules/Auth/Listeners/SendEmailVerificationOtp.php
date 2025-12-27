<?php

declare(strict_types=1);

namespace Modules\Auth\Listeners;

use Modules\Auth\Events\UserRegistered;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Modules\Shared\Services\MailService;
use Modules\Auth\Mails\VerifyEmailMail;

class SendEmailVerificationOtp implements ShouldQueue
{
    public $queue = 'notifications';

    public function __construct(protected MailService $mailService) {}

    public function handle(UserRegistered $event): void
    {
        $user = $event->user;

        $otp = (string) rand(100000, 999999);

        $cacheKey = "email_verification_otp_{$user->id}";
        Cache::put($cacheKey, $otp, 600);

        $this->mailService->sendQueue(
            $user->email,
            new VerifyEmailMail($user, $otp)
        );

        Log::info("Generated OTP for User ID {$user->id}");
    }
}