<?php

declare(strict_types=1);

namespace Modules\Shared\Http\Middlewares;

use Closure;
use Throwable;
use Illuminate\Http\Request;
use Modules\Shared\Services\ExceptionHandlerService;

class LogExceptions
{
    public function handle(Request $request, Closure $next): mixed
    {
        try {
            return $next($request);
        } catch (Throwable $e) {
            ExceptionHandlerService::handle($e, (int) auth()->id());
            throw $e;
        }
    }
}