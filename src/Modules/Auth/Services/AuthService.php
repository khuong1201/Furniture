<?php

namespace Modules\Auth\Services;

use Modules\Shared\Services\BaseService;
use Modules\User\Domain\Models\User;
use Modules\Auth\Domain\Models\RefreshToken;
use Illuminate\Support\Str;

class AuthService 
{
    public function createAccessToken(User $user): string
    {
        $header = ['alg' => config('jwt.algo'), 'typ' => 'JWT'];
        $payload = [
            'sub' => $user->id,
            'email' => $user->email,
            'iat' => time(),
            'exp' => time() + config('jwt.ttl'),
            'iss' => config('jwt.issuer'),
            'aud' => config('jwt.audience'),
            'scope' => method_exists($user, 'permissions') ? $user->permissions()->pluck('name')->toArray() : [],
        ];

        return $this->sign($header, $payload, config('jwt.secret'));
    }

    protected function sign(array $header, array $payload, string $secret): string
    {
        $headerEncoded = rtrim(strtr(base64_encode(json_encode($header)), '+/', '-_'), '=');
        $payloadEncoded = rtrim(strtr(base64_encode(json_encode($payload)), '+/', '-_'), '=');
        $signature = hash_hmac('sha256', "$headerEncoded.$payloadEncoded", $secret, true);
        $signatureEncoded = rtrim(strtr(base64_encode($signature), '+/', '-_'), '=');

        return "$headerEncoded.$payloadEncoded.$signatureEncoded";
    }

    public function createRefreshToken(User $user, ?string $deviceName, ?string $ip, ?string $userAgent): RefreshToken
    {
        return RefreshToken::create([
            'user_id' => $user->id,
            'token' => Str::random(64),
            'device_name' => $deviceName,
            'ip' => $ip,
            'user_agent' => $userAgent,
            'expires_at' => now()->addDays(30),
        ]);
    }

    public function rotateRefreshToken(string $token): ?RefreshToken
    {
        $rt = RefreshToken::where('token', $token)->first();

        if (!$rt || $rt->expires_at < now()) return null;

        $user = $rt->user; 
        if (!$user) return null;

        $newToken = $this->createRefreshToken($rt->user, $rt->device_name, $rt->ip, $rt->user_agent);
        $rt->delete();

        return $newToken;
    }

    public function revokeRefreshToken(string $token): void
    {
        RefreshToken::where('token', $token)->delete();
    }
}
