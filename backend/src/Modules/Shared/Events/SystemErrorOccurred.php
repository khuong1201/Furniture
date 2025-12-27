<?php

declare(strict_types=1);

namespace Modules\Shared\Events;

use Throwable;

class SystemErrorOccurred
{
    public function __construct(
        public string $message,
        public string $file,
        public int $line,
        public string $trace,
        public ?int $userId = null,
        public ?string $ip = null
    ) {}
    
    public static function fromThrowable(Throwable $e, ?int $userId = null): self
    {
        return new self(
            message: $e->getMessage(),
            file: $e->getFile(),
            line: $e->getLine(),
            trace: $e->getTraceAsString(),
            userId: $userId,
            ip: request()->ip()
        );
    }
}