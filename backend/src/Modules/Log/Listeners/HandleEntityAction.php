<?php

declare(strict_types=1);

namespace Modules\Log\Listeners;

use Illuminate\Contracts\Queue\ShouldQueue;
use Modules\Log\Services\LogService;
use Modules\Shared\Events\EntityActioned; 
use Throwable;

class HandleEntityAction implements ShouldQueue
{
    public $queue = 'logs'; 

    public function __construct(
        protected LogService $logService
    ) {}

    public function handle(EntityActioned $event): void
    {
        try {
            $this->logService->createLog([
                'type'       => 'audit',
                'action'     => $event->action, 
                'user_id'    => $event->userId,
                'model'      => get_class($event->model),
                'model_uuid' => $event->model->uuid ?? (string) $event->model->getKey(),
                'ip_address' => request()->ip() ?? 'CLI',
                'message'    => "User {$event->userId} {$event->action} " . class_basename($event->model),
                'metadata'   => !empty($event->changes) ? ['changes' => $event->changes] : [],
            ]);
        } catch (Throwable $e) {
            \Illuminate\Support\Facades\Log::error("Audit Log Error: " . $e->getMessage());
        }
    }
}