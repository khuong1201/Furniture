<?php

declare(strict_types=1);

namespace Modules\Permission\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Modules\Permission\Services\PermissionService;
use Modules\Shared\Exceptions\BusinessException;

class CheckPermission
{
    public function __construct(
        protected PermissionService $permissionService
    ) {}

    public function handle(Request $request, Closure $next, string $permission): mixed
    {
        $user = $request->user();

        if (!$user) {
            throw new BusinessException(401022, 'Unauthenticated');
        }

        if ($user->hasRole('super-admin')) return $next($request);

        $permissionsToCheck = explode('|', $permission);
        $userId = (int) $user->id;

        foreach ($permissionsToCheck as $perm) {
            if ($this->permissionService->hasPermission($userId, trim($perm))) {
                return $next($request);
            }
        }

        throw new BusinessException(403023);
    }
}