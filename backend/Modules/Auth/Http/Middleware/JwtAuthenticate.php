<?php

namespace Modules\Auth\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Modules\User\Domain\Models\User;
use Modules\Shared\Http\Resources\ApiResponse; 

class JwtAuthenticate
{
    protected function base64UrlDecode(string $data): string
    {
        $padding = 4 - (strlen($data) % 4);
        if ($padding < 4) {
            $data .= str_repeat('=', $padding);
        }
        return base64_decode(strtr($data, '-_', '+/'));
    }

    protected function verify(string $jwt, string $secret): ?object
    {
        $parts = explode('.', $jwt);
        if (count($parts) !== 3) return null;

        [$headerEncoded, $payloadEncoded, $signatureEncoded] = $parts;

        try {
            $header = json_decode($this->base64UrlDecode($headerEncoded));
            $payload = json_decode($this->base64UrlDecode($payloadEncoded));
        } catch (\Exception $e) {
            return null;
        }

        if (!$header || !$payload || !isset($payload->exp, $payload->sub)) return null;

        $expectedSig = hash_hmac('sha256', "$headerEncoded.$payloadEncoded", $secret, true);
        $expectedSigEncoded = rtrim(strtr(base64_encode($expectedSig), '+/', '-_'), '=');

        if (!hash_equals($expectedSigEncoded, $signatureEncoded)) return null;
        if ($payload->exp < time()) return null;

        return $payload;
    }

    public function handle(Request $request, Closure $next)
    {

        $token = $request->bearerToken();
        if (!$token) {
            return response()->json(ApiResponse::error('Token not provided', 401), 401);
        }

        $payload = $this->verify($token, config('jwt.secret'));
        if (!$payload || !isset($payload->sub)) {
             return response()->json(ApiResponse::error('Invalid or expired token', 401), 401);
        }

        $user = User::find($payload->sub);
        
        if (!$user) {
             return response()->json(ApiResponse::error('User not found', 401), 401);
        }

        // Set Auth Guard
        Auth::setUser($user);
        $request->setUserResolver(fn () => $user);

        return $next($request);
    }
}