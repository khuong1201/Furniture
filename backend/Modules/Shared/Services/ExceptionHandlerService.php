<?php
namespace Modules\Shared\Services;

use Throwable;
use Modules\Log\Events\SystemErrorLogged;

class ExceptionHandlerService
{
    public static function handle(Throwable $e, ?int $userId = null)
    {
        // Ghi log hệ thống
        event(new SystemErrorLogged(
            $userId,
            $e->getMessage(),
            request()?->ip(),
            [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => collect($e->getTrace())->take(5),
            ]
        ));
    }
}
