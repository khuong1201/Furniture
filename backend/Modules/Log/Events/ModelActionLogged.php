<?php

declare(strict_types=1);

namespace Modules\Log\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ModelActionLogged
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public ?int $userId,
        public string $action,
        public string $model,
        public string $modelUuid,
        public string $ipAddress,
        public ?array $changes = null
    ) {}
}