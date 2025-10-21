<?php

namespace Modules\Auth\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class CheckAccessTokenExpiry
{
    public function handle(Request $request, Closure $next): Response|JsonResponse
    {
        $token = $request->user()?->currentAccessToken();

        if ($token && $token->expires_at && now()->greaterThan($token->expires_at)) {
            $token->delete();
            return response()->json(['message' => 'Access token expired'], 401);
        }

        return $next($request);
    }
}
