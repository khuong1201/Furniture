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

        // Chuyển tất cả permissions cần check thành chữ thường
        $permissions = array_map('strtolower', $permissions);

        // Nếu user có bất kỳ quyền nào trong danh sách => cho qua
        $hasPermission = false;
        foreach ($permissions as $permission) {
            if ($user->hasPermission($permission)) {
                $hasPermission = true;
                break;
            }
        }

        if (!$hasPermission) {
            return response()->json([
                'status' => false,
                'message' => 'Bạn không có quyền truy cập',
            ], 403);
        }

        return $next($request);
    }
}
