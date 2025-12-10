<?php

declare(strict_types=1);

namespace Modules\Log\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class SystemErrorLogged
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public ?int $userId,
        public string $message,
        public string $ipAddress,
        public array $metadata = []
    ) {}
}