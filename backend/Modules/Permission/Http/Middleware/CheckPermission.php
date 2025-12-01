<?php

namespace Modules\Permission\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Modules\Permission\Services\PermissionService;
use Modules\Shared\Http\Resources\ApiResponse;

class CheckPermission
{
    public function __construct(
        protected PermissionService $permissionService
    ) {}

    public function handle(Request $request, Closure $next, string $permission)
    {
        $user = $request->user();
        if (!$user) {
            return response()->json(ApiResponse::error('Unauthenticated', 401), 401);
        }

        $permissionsToCheck = explode('|', $permission);
        
        foreach ($permissionsToCheck as $perm) {
            if ($this->permissionService->hasPermission($user->id, trim($perm))) {
                return $next($request);
            }
        }

        return response()->json(ApiResponse::error('Forbidden: You do not have the required permission.', 403), 403);
    }
}