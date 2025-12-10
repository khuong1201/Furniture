<?php

declare(strict_types=1);

namespace Modules\Log\Listeners;

use Modules\Log\Services\LogService;
use Modules\Log\Events\SystemErrorLogged;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Throwable;

class WriteSystemErrorLog implements ShouldQueue
{
    use InteractsWithQueue;
    
    public $queue = 'logs';

    public function __construct(protected LogService $logService) {}

    public function handle(SystemErrorLogged $event): void
    {
        try {
            $this->logService->createLog([
                'user_id'    => $event->userId,
                'type'       => 'system_error',
                'action'     => 'exception',
                'ip_address' => $event->ipAddress,
                'message'    => $event->message,
                'metadata'   => $event->metadata,
            ]);
        } catch (Throwable $e) {
            \Illuminate\Support\Facades\Log::channel('daily')->emergency('Failed to write system log to DB: ' . $e->getMessage());
        }
    }
}