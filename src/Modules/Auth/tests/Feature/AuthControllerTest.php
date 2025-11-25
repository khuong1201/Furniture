<?php

namespace Modules\Auth\Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\User\Domain\Models\User;
use Illuminate\Support\Facades\Hash;

class AuthControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_register_creates_user_and_returns_tokens()
    {
        $response = $this->postJson('/auth/register', [
            'name' => 'New User',
            'email' => 'new@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertCreated();
        $response->assertJsonStructure(['access_token', 'refresh_token', 'user']);
        $this->assertDatabaseHas('users', ['email' => 'new@example.com']);
    }

    public function test_login_returns_tokens_for_valid_credentials()
    {
        $user = User::factory()->create(['email' => 'login@example.com', 'password' => bcrypt('secret')]);

        $response = $this->postJson('/auth/login', [
            'email' => 'login@example.com',
            'password' => 'secret',
        ]);

        $response->assertOk();
        $response->assertJsonStructure(['access_token', 'refresh_token']);
    }

    public function test_login_fails_with_invalid_credentials()
    {
        $response = $this->postJson('/auth/login', [
            'email' => 'wrong@example.com',
            'password' => 'wrongpass',
        ]);

        $response->assertStatus(401);
    }

    public function test_refresh_token_returns_new_access_token()
    {
        $user = User::factory()->create();
        $token = $user->refreshTokens()->create([
            'token' => 'testtoken123',
            'expires_at' => now()->addDays(30),
        ]);

        $response = $this->postJson('/auth/refresh', ['refresh_token' => 'testtoken123']);
        $response->assertOk();
        $response->assertJsonStructure(['access_token', 'refresh_token']);
    }

    public function test_logout_deletes_refresh_token()
    {
        $user = User::factory()->create();
        $token = $user->refreshTokens()->create([
            'token' => 'logouttoken123',
            'expires_at' => now()->addDays(30),
        ]);

        $response = $this->postJson('/auth/logout', ['refresh_token' => 'logouttoken123']);
        $response->assertOk();
        $this->assertDatabaseMissing('refresh_tokens', ['token' => 'logouttoken123']);
    }
}
