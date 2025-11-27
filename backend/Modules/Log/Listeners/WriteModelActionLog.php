<?php
namespace Modules\Log\Listeners;

use Modules\Log\Domain\Models\Log;
use Modules\Log\Events\ModelActionLogged;

class WriteModelActionLog
{
    public function handle(ModelActionLogged $event)
    {
        Log::create([
            'user_id' => $event->userId,
            'type' => 'model',
            'action' => $event->action,
            'model' => $event->model,
            'model_uuid' => $event->modelUuid,
            'ip_address' => $event->ipAddress,
            'message' => $event->action . ' on ' . $event->model,
            'metadata' => $event->changes ?? [],
        ]);
    }
}