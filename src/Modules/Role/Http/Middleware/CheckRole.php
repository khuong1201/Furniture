<?php

namespace Modules\Role\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        $user = $request->user();

        if (!$user) {
            return response()->json([
                'status' => false,
                'message' => 'Bạn chưa đăng nhập',
            ], 401);
        }

        $roles = array_map('strtolower', $roles);

        $userRoles = method_exists($user, 'roles')
            ? $user->roles->pluck('name')->map(fn($r) => strtolower($r))->toArray()
            : (method_exists($user, 'role') && $user->role
                ? [strtolower($user->role->name)]
                : []);

        if (empty($userRoles) || !array_intersect($roles, $userRoles)) {
            return response()->json([
                'status' => false,
                'message' => 'Bạn không có quyền truy cập',
            ], 403);
        }

        return $next($request);
    }
}
