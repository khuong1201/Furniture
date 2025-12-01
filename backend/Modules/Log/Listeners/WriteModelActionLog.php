<?php

namespace Modules\Log\Listeners;

use Modules\Log\Domain\Models\Log;
use Modules\Log\Events\ModelActionLogged;
use Illuminate\Contracts\Queue\ShouldQueue; 

class WriteModelActionLog implements ShouldQueue
{
    public $queue = 'logs';

    public function handle(ModelActionLogged $event): void
    {
        Log::create([
            'user_id' => $event->userId,
            'type' => 'audit',
            'action' => $event->action,
            'model' => $event->model,
            'model_uuid' => $event->modelUuid,
            'ip_address' => $event->ipAddress,
            'message' => "{$event->action} on {$event->model}",
            'metadata' => $event->changes ?? [],
        ]);
    }
}