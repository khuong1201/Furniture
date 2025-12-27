<?php

declare(strict_types=1);

namespace Modules\Auth\Services;

use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Modules\Auth\Events\UserRegistered;
use Modules\User\Domain\Models\User;
use Modules\User\Domain\Repositories\UserRepositoryInterface; 
use Modules\Auth\Domain\Repositories\RefreshTokenRepositoryInterface;
use Modules\Shared\Exceptions\BusinessException;
use Modules\Role\Domain\Repositories\RoleRepositoryInterface;
use Modules\Shared\Services\BaseService;

class AuthService extends BaseService
{
    protected const REFRESH_TOKEN_TTL = 30; 

    public function __construct(
        UserRepositoryInterface $userRepo,
        protected RefreshTokenRepositoryInterface $refreshTokenRepo,
        protected RoleRepositoryInterface $roleRepo
    ) {
        parent::__construct($userRepo);
    }

    public function login(string $email, string $password, string $deviceName, string $ip, string $userAgent): array
    {
        $user = $this->repository->findByEmail($email);

        if (!$user || !Hash::check($password, $user->password)) {
            throw new BusinessException(401020, 'Tài khoản hoặc mật khẩu không chính xác'); 
        }

        if (!$user->isActive()) { 
            throw new BusinessException(423014); 
        }
        return $this->generateTokenPair($user, $deviceName, $ip, $userAgent);
    }

    public function register(array $data, string $deviceName, string $ip, string $userAgent): array
    {
        if ($this->repository->findByEmail($data['email'])) {
            throw new BusinessException(409011);
        }

        $result = DB::transaction(function () use ($data, $deviceName, $ip, $userAgent) {
            $user = $this->repository->create([
                'name'      => $data['name'],
                'email'     => $data['email'],
                'password'  => Hash::make($data['password']),
                'status'    => 'active',
            ]);

            $defaultRole = $this->roleRepo->findByName('customer');
            if ($defaultRole) {
                $user->roles()->attach($defaultRole->id);
            }

            $tokens = $this->generateTokenPair($user, $deviceName, $ip, $userAgent);

            return ['user' => $user, 'tokens' => $tokens];
        });

        event(new UserRegistered($result['user']));

        return $result['tokens'];
    }

    public function refreshToken(string $tokenStr, string $deviceName, string $ip, string $userAgent): array
    {
        $storedToken = $this->refreshTokenRepo->findByToken($tokenStr);
        if (!$storedToken || !$storedToken->isValid()) {
            throw new BusinessException(401021, 'Refresh token không hợp lệ hoặc đã hết hạn');
        }

        $user = $storedToken->user;

        $storedToken->update(['is_revoked' => true]);

        return $this->generateTokenPair($user, $deviceName, $ip, $userAgent);
    }

    public function verifyEmail(int $userId, string $otp): void
    {
        $cacheKey = "email_verification_otp_{$userId}";
        $cachedOtp = Cache::get($cacheKey);

        if (!$cachedOtp || (string)$cachedOtp !== $otp) {
            throw new BusinessException(400991, 'Mã OTP không chính xác hoặc đã hết hạn');
        }

        $user = $this->repository->findById($userId);
        
        if (!$user->hasVerifiedEmail()) {
            $user->update(['email_verified_at' => now()]);
        }

        Cache::forget($cacheKey);
    }

    public function resendOtp(int $userId): void
    {
        $user = $this->repository->findById($userId);

        if ($user->hasVerifiedEmail()) {
            throw new BusinessException(400991, 'Tài khoản này đã được xác thực trước đó');
        }

        event(new UserRegistered($user));
    }

    public function logout(User $user): void
    {
        $user->currentAccessToken()->delete();
    }

    protected function generateTokenPair(User $user, string $deviceName, string $ip, string $userAgent): array
    {
        $accessToken = $user->createToken($deviceName)->plainTextToken;

        $refreshTokenStr = hash('sha256', Str::random(60));

        $this->refreshTokenRepo->create([
            'user_id'     => $user->id,
            'token'       => $refreshTokenStr,
            'device_name' => $deviceName,
            'ip'          => $ip, 
            'user_agent'  => substr($userAgent, 0, 255),
            'expires_at'  => now()->addDays(self::REFRESH_TOKEN_TTL),
            'is_revoked'  => false
        ]);

        return [
            'access_token'  => $accessToken,
            'refresh_token' => $refreshTokenStr,
            'token_type'    => 'Bearer',
            'expires_in'    => config('sanctum.expiration') * 60,
            'user'          => [
                'id'     => $user->id,
                'name'   => $user->name,
                'email'  => $user->email,
                'avatar' => $user->getFirstMediaUrl('avatar'), 
                'roles'  => $user->roles->pluck('name'),
            ],
        ];
    }
}