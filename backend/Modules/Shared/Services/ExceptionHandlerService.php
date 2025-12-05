<?php

declare(strict_types=1);

namespace Modules\Shared\Services;

use Throwable;
use Modules\Log\Events\SystemErrorLogged; 
use Illuminate\Support\Facades\Log;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Illuminate\Support\Facades\Request;

class ExceptionHandlerService
{
    public static function handle(Throwable $e, ?int $userId = null): void
    {
        $severity = static::getSeverity($e);

        if ($severity === 'info') {
            return;
        }

        $context = static::getContext($e);

        match ($severity) {
            'critical' => Log::critical($e->getMessage(), $context),
            'error' => Log::error($e->getMessage(), $context),
            'warning' => Log::warning($e->getMessage(), $context),
            default => Log::info($e->getMessage(), $context),
        };

        if (in_array($severity, ['critical', 'error']) && class_exists(SystemErrorLogged::class)) {
            $ip = request()?->ip() ?? 'CLI/Unknown';
            
            event(new SystemErrorLogged(
                $userId,
                $e->getMessage(),
                $ip,
                $context
            ));
        }
    }

    protected static function getSeverity(Throwable $e): string
    {
        return match (true) {
            $e instanceof ValidationException => 'info',
            $e instanceof ModelNotFoundException => 'warning',
            $e instanceof BusinessException => 'warning', 
            $e instanceof HttpException && $e->getStatusCode() < 500 => 'warning',
            default => 'error',
        };
    }

    protected static function getContext(Throwable $e): array
    {
        $request = request();
        
        return [
            'exception' => get_class($e),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'code' => $e->getCode(),
            'url' => $request?->fullUrl() ?? 'CLI',
            'method' => $request?->method() ?? 'CLI',
            'user_agent' => $request?->userAgent() ?? 'Unknown',
            'trace' => collect($e->getTrace())->take(5)->toArray(),
        ];
    }
}