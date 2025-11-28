<?php

namespace Modules\Permission\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CheckPermission
{
    public function handle(Request $request, Closure $next, string $permission)
    {
        $user = $request->user();

        if (! $user) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        if (method_exists($user, 'hasPermission')) {
            if (! $user->hasPermission($permission)) {
                return response()->json(['message' => 'Forbidden.'], 403);
            }
            return $next($request);
        }

        if (app()->bound(\Modules\Permission\Services\PermissionService::class)) {
            $service = app(\Modules\Permission\Services\PermissionService::class);

            if ($service->userHasPermission($user->id, $permission)) {
                return $next($request);
            }
        }

        return response()->json(['message' => 'Forbidden.'], 403);
    }
}
