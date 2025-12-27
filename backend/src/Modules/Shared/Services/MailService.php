<?php

declare(strict_types=1);

namespace Modules\Shared\Services;

use Illuminate\Contracts\Mail\Mailable;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class MailService
{
    public function sendQueue(string $to, Mailable $mailable): void
    {
        Mail::to($to)->queue($mailable);
        
        Log::info("Mail queued", [
            'to' => $to,
            'mailable' => get_class($mailable)
        ]);
    }
}