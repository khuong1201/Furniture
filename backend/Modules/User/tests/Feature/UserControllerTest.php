<?php

namespace Modules\User\Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\User\Domain\Models\User;
use Modules\Role\Domain\Models\Role;
use Modules\Auth\Domain\Models\RefreshToken;
use Illuminate\Support\Str;

class UserControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function createAuthUser(): User
    {
        $user = User::factory()->create();
        // nếu dùng JWT của module Auth, tạo access token phù hợp hoặc set auth guard
        // simplest: set acting user via auth()->setUser when middleware allows it in tests
        return $user;
    }

    public function test_index_returns_paginated_users_and_filters()
    {
        User::factory()->count(3)->create(['name' => 'Alice']);
        User::factory()->create(['name' => 'Bob']);

        $user = $this->createAuthUser();

        $response = $this->getJson('/users?per_page=2&q=Alice', $this->authHeaderFor($user));
        $response->assertOk()->assertJsonStructure(['data', 'meta']);
        $this->assertCount(2, $response->json('data'));
    }

    public function test_show_returns_user()
    {
        $user = User::factory()->create();
        $actor = $this->createAuthUser();

        $response = $this->getJson("/users/{$user->uuid}", $this->authHeaderFor($actor));
        $response->assertOk()->assertJsonFragment(['uuid' => $user->uuid]);
    }

    public function test_store_validates_and_creates_user()
    {
        $actor = $this->createAuthUser();

        $payload = [
            'name' => 'New User',
            'email' => 'newuser@example.test',
            'password' => 'secret123',
        ];

        $response = $this->postJson('/users', $payload, $this->authHeaderFor($actor));
        $response->assertStatus(201)->assertJsonStructure(['user']);
        $this->assertDatabaseHas('users', ['email' => 'newuser@example.test']);
    }

    public function test_update_changes_user_and_syncs_roles()
    {
        $user = User::factory()->create();
        $role = Role::factory()->create();
        $actor = $this->createAuthUser();

        $payload = [
            'name' => 'Updated Name',
            'roles' => [$role->id],
            'password' => 'newpass123'
        ];

        $response = $this->putJson("/users/{$user->uuid}", $payload, $this->authHeaderFor($actor));
        $response->assertOk()->assertJsonStructure(['user']);
        $this->assertDatabaseHas('role_user', ['role_id' => $role->id, 'user_id' => $user->id]);
    }

    public function test_destroy_deletes_user()
    {
        $user = User::factory()->create();
        $actor = $this->createAuthUser();

        $response = $this->deleteJson("/users/{$user->uuid}", [], $this->authHeaderFor($actor));
        $response->assertOk();
        $this->assertDatabaseMissing('users', ['uuid' => $user->uuid]);
    }

    /**
     * Helpers to produce Authorization header for tests.
     * Adjust to your JWT creation logic.
     */
    protected function authHeaderFor(User $user): array
    {
        // Option A: if JwtAuthenticate respects Auth::setUser in tests, simply:
        // auth()->setUser($user); return [];
        // Option B: create JWT access token matching module Auth TokenService
        // Below is a fallback to set request user resolver so middleware sees user
        auth()->setUser($user);
        return [];
    }
}
