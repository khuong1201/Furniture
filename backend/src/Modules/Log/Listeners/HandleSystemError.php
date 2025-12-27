<?php

declare(strict_types=1);

namespace Modules\Log\Listeners;

use Illuminate\Contracts\Queue\ShouldQueue;
use Modules\Log\Services\LogService;
use Modules\Shared\Events\SystemErrorOccurred;

class HandleSystemError implements ShouldQueue
{
    public $queue = 'logs';

    public function __construct(
        protected LogService $logService
    ) {}

    public function handle(SystemErrorOccurred $event): void
    {
        $this->logService->createLog([
            'type'       => 'system_error',
            'action'     => 'exception',
            'user_id'    => $event->userId,
            'ip_address' => $event->ip,
            'message'    => $event->message,
            'metadata'   => [
                'file'  => $event->file,
                'line'  => $event->line,
                'trace' => substr($event->trace, 0, 2000) 
            ],
        ]);
    }
}