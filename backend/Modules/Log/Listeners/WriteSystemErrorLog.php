<?php

namespace Modules\Log\Listeners;

use Modules\Log\Domain\Models\Log;
use Modules\Log\Events\SystemErrorLogged;
use Illuminate\Contracts\Queue\ShouldQueue;

class WriteSystemErrorLog implements ShouldQueue
{
    public $queue = 'logs';

    public function handle(SystemErrorLogged $event): void
    {
        Log::create([
            'user_id' => $event->userId,
            'type' => 'system_error',
            'action' => 'exception',
            'ip_address' => $event->ipAddress,
            'message' => substr($event->message, 0, 1000),
            'metadata' => $event->metadata,
        ]);
    }
}