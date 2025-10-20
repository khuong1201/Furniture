<?php

namespace Modules\Permission\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckPermission
{
    public function handle(Request $request, Closure $next, ...$permissions): Response
    {
        $user = $request->user();

        if (!$user) {
            return response()->json([
                'status' => false,
                'message' => 'Bạn chưa đăng nhập',
            ], 401);
        }

        $permissions = array_map('strtolower', $permissions);

        $userPermissions = method_exists($user, 'permissions')
            ? $user->permissions->pluck('name')->map(fn($p) => strtolower($p))->toArray()
            : (method_exists($user, 'hasPermission')
                ? $user->getAllPermissions()
                : []);

        if (empty($userPermissions) || !array_intersect($permissions, $userPermissions)) {
            return response()->json([
                'status' => false,
                'message' => 'Bạn không có quyền truy cập',
            ], 403);
        }

        return $next($request);
    }
}
