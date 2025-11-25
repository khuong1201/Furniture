<?php

namespace Modules\Role\Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Role\Domain\Models\Role;
use Modules\User\Domain\Models\User;

class RoleControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        // Nếu muốn bypass middleware, dùng withoutMiddleware(); nếu muốn test RBAC thật, remove dòng dưới và seed permission
        $this->withoutMiddleware();
    }

    protected function actingAsTestUser(): User
    {
        $user = User::factory()->create();
        $this->actingAs($user, 'web');
        return $user;
    }

    public function test_index_returns_paginated_roles()
    {
        Role::factory()->count(5)->create();
        $this->actingAsTestUser();

        $res = $this->getJson(route('role.index', ['per_page' => 2]));

        $res->assertStatus(200)
            ->assertJsonStructure(['data', 'meta']);

        $this->assertCount(2, $res->json('data'));
    }

    public function test_store_creates_role_and_returns_201()
    {
        $this->actingAsTestUser();

        $payload = ['name' => 'manager', 'description' => 'Manager role'];

        $res = $this->postJson(route('role.store'), $payload);

        $res->assertStatus(201)
            ->assertJsonFragment(['name' => 'manager']);

        $this->assertDatabaseHas('roles', ['name' => 'manager']);
    }

    public function test_show_returns_role()
    {
        $role = Role::factory()->create(['name' => 'viewer']);
        $this->actingAsTestUser();

        $res = $this->getJson(route('role.show', $role));

        $res->assertStatus(200)
            ->assertJsonFragment(['name' => 'viewer']);
    }

    public function test_update_changes_role()
    {
        $role = Role::factory()->create(['name' => 'old-name']);
        $this->actingAsTestUser();

        $payload = ['name' => 'new-name'];

        $res = $this->putJson(route('role.update', $role), $payload);

        $res->assertStatus(200)
            ->assertJsonFragment(['name' => 'new-name']);

        $this->assertDatabaseHas('roles', ['id' => $role->id, 'name' => 'new-name']);
    }

    public function test_destroy_deletes_role()
    {
        $role = Role::factory()->create();
        $this->actingAsTestUser();

        $res = $this->deleteJson(route('role.destroy', $role));

        $res->assertStatus(200);
        $this->assertDatabaseMissing('roles', ['id' => $role->id]);
    }
}