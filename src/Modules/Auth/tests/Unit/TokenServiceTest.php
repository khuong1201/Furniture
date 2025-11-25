<?php

namespace Modules\Auth\Tests\Unit;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Auth\Services\TokenService;
use Modules\User\Domain\Models\User;
use Modules\Auth\Domain\Models\RefreshToken;

class TokenServiceTest extends TestCase
{
    use RefreshDatabase;

    protected TokenService $tokenService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->tokenService = app(TokenService::class);
    }

    public function test_create_access_token_returns_valid_jwt()
    {
        $user = User::factory()->create();
        $token = $this->tokenService->createAccessToken($user);

        $this->assertIsString($token);
        $parts = explode('.', $token);
        $this->assertCount(3, $parts);

        $payload = json_decode(base64_decode(strtr($parts[1], '-_', '+/')), true);
        $this->assertEquals($user->id, $payload['sub']);
        $this->assertEquals($user->email, $payload['email']);
        $this->assertTrue($payload['exp'] > time());
    }

    public function test_create_refresh_token_persists_to_database()
    {
        $user = User::factory()->create();
        $refreshToken = $this->tokenService->createRefreshToken($user, 'iPhone', '127.0.0.1', 'Safari');

        $this->assertInstanceOf(RefreshToken::class, $refreshToken);
        $this->assertDatabaseHas('refresh_tokens', ['token' => $refreshToken->token]);
    }

    public function test_rotate_refresh_token_replaces_old_token()
    {
        $user = User::factory()->create();
        $oldToken = $this->tokenService->createRefreshToken($user, 'MacBook', '127.0.0.1', 'Chrome');

        $newToken = $this->tokenService->rotateRefreshToken($oldToken->token);

        $this->assertNotEquals($oldToken->token, $newToken->token);
        $this->assertDatabaseMissing('refresh_tokens', ['token' => $oldToken->token]);
        $this->assertDatabaseHas('refresh_tokens', ['token' => $newToken->token]);
    }

    public function test_revoke_refresh_token_deletes_token()
    {
        $user = User::factory()->create();
        $token = $this->tokenService->createRefreshToken($user, 'PC', '127.0.0.1', 'Firefox');

        $this->tokenService->revokeRefreshToken($token->token);
        $this->assertDatabaseMissing('refresh_tokens', ['token' => $token->token]);
    }
}
