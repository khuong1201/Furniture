<?php
namespace Modules\Shared\Http\Middlewares;

use Closure;
use Throwable;
use Modules\Shared\Services\ExceptionHandlerService;

class LogExceptions
{
    public function handle($request, Closure $next)
    {
        try {
            return $next($request);
        } catch (Throwable $e) {
            ExceptionHandlerService::handle($e, auth()->id() ?? null);
            throw $e;
        }
    }
}
