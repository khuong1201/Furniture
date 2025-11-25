<?php

namespace Modules\Auth\Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\User\Domain\Models\User;
use Modules\Auth\Services\TokenService;

class JwtMiddlewareTest extends TestCase
{
    use RefreshDatabase;

    public function test_valid_token_allows_request()
    {
        $user = User::factory()->create();
        $token = app(TokenService::class)->createAccessToken($user);

        $response = $this->getJson('/protected-route', [
            'Authorization' => "Bearer $token",
        ]);

        $response->assertOk();
    }

    public function test_invalid_token_returns_unauthorized()
    {
        $response = $this->getJson('/protected-route', [
            'Authorization' => "Bearer invalid.token.here",
        ]);

        $response->assertStatus(401);
    }

    public function test_missing_token_returns_unauthorized()
    {
        $response = $this->getJson('/protected-route');
        $response->assertStatus(401);
    }
}
