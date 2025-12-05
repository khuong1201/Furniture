<?php

declare(strict_types=1);

namespace Modules\Shared\Services;

use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Illuminate\Contracts\Mail\Mailable;
use Exception;

class MailService
{
    public function send(mixed $to, Mailable $mailable): bool
    {
        $emailAddress = is_object($to) ? ($to->email ?? 'unknown') : $to;

        try {
            Mail::to($to)->send($mailable);
            
            Log::info("Email sent successfully", [
                'to' => $emailAddress,
                'class' => get_class($mailable)
            ]);
            
            return true;
        } catch (Exception $e) {
            Log::error("Failed to send email", [
                'to' => $emailAddress,
                'mailable' => get_class($mailable),
                'error' => $e->getMessage()
            ]);
            
            return false;
        }
    }
}