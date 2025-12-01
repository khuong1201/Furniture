<?php

namespace Modules\Notification\Listeners;

use Modules\Auth\Events\UserRegistered; 
use Modules\Notification\Notifications\VerifyEmailNotification;
use Illuminate\Contracts\Queue\ShouldQueue;

class SendOtpOnRegister implements ShouldQueue
{
    public function handle(UserRegistered $event): void
    {
        $user = $event->user;

        $otp = $user->verification_code;

        if ($otp) {
            $user->notify(new VerifyEmailNotification($otp));
        }
    }
}