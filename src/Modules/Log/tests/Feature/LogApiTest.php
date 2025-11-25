<?php

namespace Modules\Log\tests\Feature;

use Tests\TestCase;
use Modules\Log\Domain\Models\Log;
use Modules\User\Domain\Models\User;
use Modules\Role\Domain\Models\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;

class LogApiTest extends TestCase
{
    use RefreshDatabase;

    private string $adminRoleName = 'admin';
    private string $userRoleName = 'user';

    protected function setUp(): void
    {
        parent::setUp();

        // Tạo roles
        Role::firstOrCreate(['name' => $this->adminRoleName]);
        Role::firstOrCreate(['name' => $this->userRoleName]);

        // Tạo users
        $admin = User::factory()->create();
        $admin->roles()->attach(Role::where('name', $this->adminRoleName)->first());

        $user = User::factory()->create();
        $user->roles()->attach(Role::where('name', $this->userRoleName)->first());

        // Tạo logs
        Log::factory()->count(3)->create();

        // Bypass JWT + Permission middleware
        $this->withoutMiddleware([
            \Modules\Auth\Http\Middleware\JwtAuthenticate::class,
            \Modules\Permission\Http\Middleware\CheckPermission::class
        ]);
    }

    public function test_admin_can_get_logs()
    {
        $admin = User::whereHas('roles', fn($q) => $q->where('name', $this->adminRoleName))->first();

        $response = $this->actingAs($admin, 'api')->getJson('/api/logs');

        $response->assertStatus(200);
        $response->assertJsonCount(3, 'data'); // tất cả logs
    }

    public function test_non_admin_can_get_logs_when_middleware_bypassed()
    {
        $user = User::whereHas('roles', fn($q) => $q->where('name', $this->userRoleName))->first();

        $response = $this->actingAs($user, 'api')->getJson('/api/logs');

        $response->assertStatus(200); // middleware bị bypass → non-admin cũng được
        $response->assertJsonCount(3, 'data');
    }

    public function test_unauthenticated_can_get_logs_when_middleware_bypassed()
    {
        $response = $this->getJson('/api/logs');

        $response->assertStatus(200); // middleware bị bypass → không cần token
        $response->assertJsonCount(3, 'data');
    }

    public function test_can_filter_logs_via_api()
    {
        $admin = User::whereHas('roles', fn($q) => $q->where('name', $this->adminRoleName))->first();

        // Tạo thêm 2 log type 'system'
        Log::factory()->count(2)->create(['type' => 'system']);

        $response = $this->actingAs($admin, 'api')->getJson('/api/logs?type=system');

        $response->assertStatus(200);
        $response->assertJsonCount(2, 'data'); // chỉ 2 log type system
    }
}
