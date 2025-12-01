<?php

namespace Modules\Shared\Services;

use Throwable;
use Modules\Log\Events\SystemErrorLogged;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpException;

class ExceptionHandlerService
{
    public static function handle(Throwable $e, ?int $userId = null): void
    {
        $severity = static::getSeverity($e);
        $context = static::getContext($e);

        match ($severity) {
            'critical' => Log::critical($e->getMessage(), $context),
            'error' => Log::error($e->getMessage(), $context),
            'warning' => Log::warning($e->getMessage(), $context),
            default => Log::info($e->getMessage(), $context),
        };

        if (in_array($severity, ['critical', 'error'])) {
            event(new SystemErrorLogged(
                $userId,
                $e->getMessage(),
                request()?->ip(),
                $context
            ));
        }
    }

    protected static function getSeverity(Throwable $e): string
    {
        return match (true) {
            $e instanceof ValidationException => 'info',
            $e instanceof ModelNotFoundException => 'warning',
            $e instanceof HttpException && $e->getStatusCode() < 500 => 'warning',
            default => 'error',
        };
    }
    protected static function getContext(Throwable $e): array
    {
        return [
            'exception' => get_class($e),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'code' => $e->getCode(),
            'url' => request()?->fullUrl(),
            'method' => request()?->method(),
            'user_agent' => request()?->userAgent(),
            'trace' => collect($e->getTrace())->take(5)->toArray(),
        ];
    }
}