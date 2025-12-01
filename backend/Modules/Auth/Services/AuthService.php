<?php

namespace Modules\Auth\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Str;
use Modules\User\Domain\Models\User;
use Modules\Auth\Domain\Models\RefreshToken;
use Modules\Auth\Domain\Repositories\AuthRepositoryInterface;
use Modules\Role\Domain\Repositories\RoleRepositoryInterface; 
use Modules\Auth\Events\UserRegistered;
class AuthService 
{
    public function __construct(
        protected AuthRepositoryInterface $authRepo
    ) {}

    public function register(array $data, ?string $ip, ?string $userAgent): array
    {
        return DB::transaction(function () use ($data, $ip, $userAgent) {
            $otp = (string) random_int(100000, 999999);

            $user = $this->authRepo->create([
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => Hash::make($data['password']),
                'verification_code' => $otp,
                'verification_expires_at' => now()->addMinutes(10),
                'is_active' => false, 
            ]);

            if (interface_exists(RoleRepositoryInterface::class)) {
                $role = \Modules\Role\Models\Role::where('name', 'user')->first();
                if ($role) {
                    $user->roles()->attach($role->id);
                }
            }
            
            event(new UserRegistered($user));

            return [
                'user' => $user->only(['id', 'uuid', 'email', 'name']),
                'message' => 'Registration successful. Please check your email to verify your account.',
                'require_verification' => true
            ];
        });
    }

    public function verifyOtp(string $email, string $otp): array
    {
        $user = $this->authRepo->findByEmail($email);

        if (!$user) {
            throw ValidationException::withMessages(['email' => 'User not found.']);
        }

        if ($user->is_active) {
             return ['message' => 'Account already verified.'];
        }

        if ($user->verification_code !== $otp) {
            throw ValidationException::withMessages(['otp' => 'Invalid verification code.']);
        }

        if ($user->verification_expires_at && $user->verification_expires_at->isPast()) {
            throw ValidationException::withMessages(['otp' => 'Verification code expired.']);
        }

        $user->update([
            'email_verified_at' => now(),
            'is_active' => true,
            'verification_code' => null,
            'verification_expires_at' => null,
        ]);

        return $this->generateAuthResponse($user, null, request()->ip(), request()->userAgent());
    }

    public function login(string $email, string $password, ?string $deviceName, ?string $ip, ?string $userAgent): array
    {
        $user = $this->authRepo->findByEmail($email);

        if (!$user || !Hash::check($password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['Invalid credentials.'],
            ]);
        }

        return $this->generateAuthResponse($user, $deviceName, $ip, $userAgent);
    }

    private function generateAuthResponse(User $user, ?string $deviceName, ?string $ip, ?string $userAgent): array
    {
        $accessToken = $this->createAccessToken($user);
        $refreshToken = $this->createRefreshToken($user, $deviceName, $ip, $userAgent);

        $permissions = [];
        if (method_exists($user, 'permissions')) {
            $permissions = $user->permissions->pluck('name')->unique()->values()->toArray();
        }

        return [
            'user' => $user->only(['id', 'uuid', 'email', 'name']),
            'access_token' => $accessToken,
            'refresh_token' => $refreshToken->token,
            'permissions' => $permissions,
            'expires_in' => config('jwt.ttl'),
        ];
    }

    public function createAccessToken(User $user): string
    {
        $header = ['alg' => config('jwt.algo', 'HS256'), 'typ' => 'JWT'];
        $payload = [
            'sub' => $user->id,
            'email' => $user->email,
            'iat' => time(),
            'exp' => time() + config('jwt.ttl', 3600),
            'iss' => config('jwt.issuer', config('app.url')),
        ];

        return $this->sign($header, $payload, config('jwt.secret'));
    }

    public function createRefreshToken(User $user, ?string $deviceName, ?string $ip, ?string $userAgent): RefreshToken
    {
        if ($deviceName) {
            RefreshToken::where('user_id', $user->id)->where('device_name', $deviceName)->delete();
        }

        return RefreshToken::create([
            'user_id' => $user->id,
            'token' => Str::random(64),
            'device_name' => $deviceName,
            'ip' => $ip,
            'user_agent' => $userAgent,
            'expires_at' => now()->addDays(config('jwt.refresh_ttl', 30)),
        ]);
    }

    public function rotateRefreshToken(string $token): array
    {
        $rt = RefreshToken::where('token', $token)->first();

        if (!$rt || $rt->expires_at < now()) {
             throw new \Illuminate\Auth\AuthenticationException('Invalid or expired refresh token');
        }

        $user = $rt->user;
        
        $rt->delete();

        return $this->generateAuthResponse($user, $rt->device_name, $rt->ip, $rt->user_agent);
    }

    public function revokeRefreshToken(string $token): void
    {
        RefreshToken::where('token', $token)->delete();
    }

    protected function sign(array $header, array $payload, string $secret): string
    {
        $headerEncoded = $this->base64UrlEncode(json_encode($header));
        $payloadEncoded = $this->base64UrlEncode(json_encode($payload));
        $signature = hash_hmac('sha256', "$headerEncoded.$payloadEncoded", $secret, true);
        $signatureEncoded = $this->base64UrlEncode($signature);

        return "$headerEncoded.$payloadEncoded.$signatureEncoded";
    }

    protected function base64UrlEncode(string $data): string
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }
}