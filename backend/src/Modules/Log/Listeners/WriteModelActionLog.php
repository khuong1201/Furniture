<?php

declare(strict_types=1);

namespace Modules\Log\Listeners;

use Modules\Log\Services\LogService;
use Modules\Log\Events\ModelActionLogged;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Throwable;

class WriteModelActionLog implements ShouldQueue
{
    use InteractsWithQueue;

    public $queue = 'logs';

    public $tries = 3;

    public function __construct(protected LogService $logService) {}

    public function handle(ModelActionLogged $event): void
    {
        try {
            $this->logService->createLog([
                'user_id'    => $event->userId,
                'type'       => 'audit',
                'action'     => $event->action,
                'model'      => $event->model,
                'model_uuid' => $event->modelUuid,
                'ip_address' => $event->ipAddress,
                'message'    => "{$event->action} on class " . class_basename($event->model),
                'metadata'   => $event->changes ? ['changes' => $event->changes] : [],
            ]);
        } catch (Throwable $e) {
            \Illuminate\Support\Facades\Log::channel('daily')->error('Failed to write audit log: ' . $e->getMessage());
        }
    }
}