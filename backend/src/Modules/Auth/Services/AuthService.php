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
use Modules\Auth\Domain\Repositories\AuthRepositoryInterface;
use Modules\Auth\Domain\Repositories\RefreshTokenRepositoryInterface;
use Modules\Shared\Exceptions\BusinessException;
use Modules\Role\Domain\Repositories\RoleRepositoryInterface;

class AuthService
{
    protected const REFRESH_TOKEN_TTL = 30; 

    public function __construct(
        protected AuthRepositoryInterface $userRepo,
        protected RefreshTokenRepositoryInterface $refreshTokenRepo,
        protected RoleRepositoryInterface $roleRepo
    ) {}

    public function login(array $credentials, string $deviceName = 'web'): array
    {
        if (!Auth::attempt(['email' => $credentials['email'], 'password' => $credentials['password']])) {
            throw new BusinessException('Thông tin đăng nhập không chính xác.', 401);
        }

        $user = Auth::user();

        if (!$user->is_active) {
            throw new BusinessException('Tài khoản đã bị khóa.', 403);
        }

        return $this->generateTokenPair($user, $deviceName);
    }

    public function register(array $data, string $deviceName = 'web'): array
    {
        $result = DB::transaction(function () use ($data, $deviceName) {
            $userData = [
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => Hash::make($data['password']),
                'is_active' => true,
            ];
            
            /** @var User $user */
            $user = $this->userRepo->create($userData);

            $defaultRole = $this->roleRepo->findByName('customer');
            if ($defaultRole) {
                $user->roles()->attach($defaultRole->id);
                $user->clearPermissionCache();
            }

            $tokens = $this->generateTokenPair($user, $deviceName);

            return ['user' => $user, 'tokens' => $tokens];
        });

        $user = $result['user'];
        $tokens = $result['tokens'];

        event(new UserRegistered($user));

        return $tokens;
    }

    public function refreshToken(string $refreshTokenStr, string $deviceName = 'web'): array
    {
        $storedToken = $this->refreshTokenRepo->findByToken($refreshTokenStr);

        if (!$storedToken || !$storedToken->isValid()) {
            throw new BusinessException('Refresh token không hợp lệ hoặc đã hết hạn.', 401);
        }

        $user = $storedToken->user;

        $storedToken->update(['is_revoked' => true]);

        return $this->generateTokenPair($user, $deviceName);
    }

    public function verifyEmail(int $userId, string $otp): void
    {
        $cacheKey = "email_verification_otp_{$userId}";
        $cachedOtp = Cache::get($cacheKey);

        // 1. Kiểm tra OTP có khớp không
        if (!$cachedOtp || (string)$cachedOtp !== (string)$otp) {
            throw new BusinessException('Mã xác thực không đúng hoặc đã hết hạn.', 400);
        }

        // 2. Cập nhật User
        $user = $this->userRepo->findById($userId);
        
        if (!$user->hasVerifiedEmail()) {
            $user->update(['email_verified_at' => now()]);
        }

        Cache::forget($cacheKey);
    }

    public function resendOtp(int $userId): void
    {
        $user = $this->userRepo->findById($userId);
        
        if ($user->hasVerifiedEmail()) {
            throw new BusinessException('Tài khoản này đã được xác thực trước đó.', 400);
        }
        
        event(new UserRegistered($user));
    }

    public function logout(User $user): void
    {
        $user->currentAccessToken()->delete();
    }
    
    public function logoutAll(User $user): void
    {
        $user->tokens()->delete();
        $this->refreshTokenRepo->revokeAllForUser($user->id);
    }

    protected function generateTokenPair(User $user, string $deviceName): array
    {
        $accessToken = $user->createToken($deviceName)->plainTextToken;

        $refreshTokenStr = hash('sha256', Str::random(60));
        
        $this->refreshTokenRepo->create([
            'user_id' => $user->id,
            'token' => $refreshTokenStr,
            'device_name' => $deviceName,
            'ip' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'expires_at' => now()->addDays(self::REFRESH_TOKEN_TTL),
            'is_revoked' => false
        ]);

        return [
            'access_token' => $accessToken,
            'refresh_token' => $refreshTokenStr,
            'token_type' => 'Bearer',
            'user' => $this->formatUserResponse($user),
        ];
    }

    protected function formatUserResponse(User $user): array
    {
        return [
            'id' => $user->id,
            'uuid' => $user->uuid,
            'name' => $user->name,
            'email' => $user->email,
            'avatar_url' => $user->avatar_url,
            'roles' => $user->roles->pluck('name'),
            'permissions' => $user->allPermissions()->pluck('name'),
        ];
    }
}