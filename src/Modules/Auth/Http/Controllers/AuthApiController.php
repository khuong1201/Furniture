<?php

namespace Modules\Auth\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Modules\User\Models\User;
use Modules\Auth\Models\RefreshToken;

class AuthApiController extends Controller
{
    /**
     * Đăng ký tài khoản
     */
    public function register(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users',
            'password' => 'required|string|min:6',
        ]);
        if (User::where('email', $validated['email'])->exists()) {
            return response()->json(['message' => 'Email đã tồn tại'], 422);
        }
        $user = User::create([
            'uuid' => Str::uuid(),
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
        ]);
        
        $roleId = DB::table('roles')->where('name', 'customer')->value('id');
        if (!$roleId) {
            $roleId = DB::table('roles')->insertGetId([
                'name' => 'customer',
                'label' => 'Khách hàng',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
        if (!DB::table('role_user')->where(['user_id' => $user->id, 'role_id' => $roleId])->exists()) {
            DB::table('role_user')->insert([
                'user_id' => $user->id,
                'role_id' => $roleId,
            ]);
        }

        $accessToken = $user->createToken('access_token') ->plainTextToken;
        $token = $user->tokens()->latest()->first();
        $token->update(['expires_at' => now()->addMinutes(15)]);

        $refreshPlain = Str::random(64);
        RefreshToken::create([
            'user_id' => $user->id,
            'token' => hash('sha256', $refreshPlain),
            'device_name' => $validated['device_name'] ?? $request->header('User-Agent') ?? 'unknown',
            'ip' => $request->ip(),
            'user_agent' => $request->header('User-Agent'),
            'expires_at' => now()->addDays(30),
        ]);

        return response()->json([
            'user' => $user,
            'access_token' => $accessToken,
            'refresh_token' => $refreshPlain,
            'token_type' => 'Bearer',
        ]);
    }

    /**
     * Đăng nhập
     */
    public function login(Request $request)
    {
        $validated = $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        $user = User::where('email', $validated['email'])->first();

        if (!$user || !Hash::check($validated['password'], $user->password)) {
            return response()->json(['message' => 'Sai thông tin đăng nhập'], 401);
        }

        $user->tokens()->delete();

        $accessToken = $user->createToken('access_token')->plainTextToken;
        $token = $user->tokens()->latest()->first();
        $token->update(['expires_at' => now()->addMinutes(15)]);

        // Tạo refresh token 30 ngày
        $refreshToken = Str::random(64);
        RefreshToken::create([
            'user_id' => $user->id,
            'token' => hash('sha256', $refreshToken),
            'expires_at' => now()->addDays(30),
        ]);

        return response()->json([
            'user' => $user,
            'access_token' => $accessToken,
            'refresh_token' => $refreshToken,
            'token_type' => 'Bearer',
        ]);
    }

    /**
     * Refresh access token bằng refresh token
     */
    public function refresh(Request $request)
    {
        $request->validate(['refresh_token' => 'required|string']);

        $hashed = hash('sha256', $request->refresh_token);
        $refresh = RefreshToken::where('token', $hashed)->first();

        if (!$refresh || now()->greaterThan($refresh->expires_at)) {
            return response()->json(['message' => 'Refresh token không hợp lệ hoặc đã hết hạn'], 401);
        }

        $user = $refresh->user;

        // Xóa access token cũ (nếu muốn)
        $user->tokens()->delete();

        // Tạo access token mới
        $accessToken = $user->createToken('access_token')->plainTextToken;
        $token = $user->tokens()->latest()->first();
        $token->update(['expires_at' => now()->addMinutes(15)]);

        return response()->json([
            'access_token' => $accessToken,
            'token_type' => 'Bearer',
        ]);
    }

    /**
     * Đăng xuất
     */
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        return response()->json(['message' => 'Đã đăng xuất']);
    }

    /**
     * Lấy thông tin user
     */
    public function me(Request $request)
    {
        return response()->json($request->user());
    }
}
