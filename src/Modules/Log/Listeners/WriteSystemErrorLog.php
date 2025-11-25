<?php
namespace Modules\Log\Listeners;

use Modules\Log\Domain\Models\Log;
use Modules\Log\Events\SystemErrorLogged;

class WriteSystemErrorLog
{
    public function handle(SystemErrorLogged $event)
    {
        Log::create([
            'user_id' => $event->userId,
            'type' => 'system',
            'action' => 'error',
            'model' => null,
            'model_uuid' => null,
            'ip_address' => $event->ipAddress,
            'message' => $event->message,
            'metadata' => $event->metadata,
        ]);
    }
}