<?php

namespace Modules\Permission\tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;
use Modules\Permission\Domain\Models\Permission;
use Modules\Role\Domain\Models\Role;
use Modules\User\Domain\Models\User;
use Modules\Permission\Providers\PermissionServiceProvider;
use Modules\Auth\Http\Middleware\JwtAuthenticate;
use Modules\Permission\Http\Middleware\CheckPermission;

class PermissionControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        if (class_exists(PermissionServiceProvider::class)) {
            $this->app->register(PermissionServiceProvider::class);
        }

        $routesPath = base_path('Modules/Permission/Http/Routes/api.php');
        if (file_exists($routesPath)) {
            require $routesPath;
        } else {
            Route::prefix('api/admin')
                ->middleware([JwtAuthenticate::class, 'permission:manage_permissions'])
                ->group(function () {
                    Route::get('permissions', [\Modules\Permission\Http\Controllers\PermissionController::class, 'index'])->name('permission.index');
                    Route::post('permissions', [\Modules\Permission\Http\Controllers\PermissionController::class, 'store'])->name('permission.store');
                    Route::get('permissions/{permission}', [\Modules\Permission\Http\Controllers\PermissionController::class, 'show'])->name('permission.show');
                });
        }

        $this->app['router']->aliasMiddleware('permission', CheckPermission::class);
    }

    public function test_index_requires_authentication()
    {
        $response = $this->getJson('/api/admin/permissions');
        $response->assertStatus(401);
    }

    public function test_index_returns_permissions_for_authenticated_user()
    {
        $this->withoutMiddleware(JwtAuthenticate::class);

        // Tạo user bằng forceFill để đảm bảo các trường NOT NULL (uuid, ...) được thiết lập
        $user = new User();
        $user->forceFill([
            'name' => 'test user',
            'email' => 'user@example.com',
            'password' => bcrypt('password'),
            'uuid' => Str::uuid()->toString(),
        ])->save();

        $role = Role::query()->create([
            'name' => 'admin',
            'description' => 'admin role',
        ]);

        $permissionManage = Permission::query()->create([
            'name' => 'manage_permissions',
            'description' => 'Manage permissions',
        ]);

        $permissionView = Permission::query()->create([
            'name' => 'view_dashboard',
            'description' => 'View dashboard',
        ]);

        DB::table('role_user')->insert([
            'role_id' => $role->id,
            'user_id' => $user->id,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('permission_role')->insert([
            ['role_id' => $role->id, 'permission_id' => $permissionManage->id, 'created_at' => now(), 'updated_at' => now()],
            ['role_id' => $role->id, 'permission_id' => $permissionView->id, 'created_at' => now(), 'updated_at' => now()],
        ]);

        // --- DEBUG CHECKS: nếu một trong các assert này fail, biết ngay nguyên nhân ---
        $this->assertDatabaseHas('role_user', ['role_id' => $role->id, 'user_id' => $user->id]);
        $this->assertDatabaseHas('permission_role', ['role_id' => $role->id, 'permission_id' => $permissionManage->id]);
        // Kiểm tra method hasPermission hoạt động tại runtime
        $this->assertTrue(
            $user->fresh()->hasPermission('manage_permissions'),
            'User->hasPermission returned false; kiểm tra Role::permissions relation và pivot data'
        );
        // -----------------------------------------------------------------------

        // Nếu bạn muốn bypass middleware permission tạm thời để kiểm tra controller logic,
        // uncomment dòng dưới:
        // $this->withoutMiddleware(CheckPermission::class);

        $this->actingAs($user);

        $response = $this->getJson('/api/admin/permissions');

        $response->assertStatus(200);

        $data = $response->json();
        $this->assertIsArray($data);
        $this->assertContains('manage_permissions', array_map('strtolower', $data));
        $this->assertContains('view_dashboard', array_map('strtolower', $data));
    }

    public function test_store_creates_permission()
    {
        $this->withoutMiddleware([JwtAuthenticate::class, CheckPermission::class]);

        $user = new User();
        $user->forceFill([
            'name' => 'creator',
            'email' => 'creator@example.com',
            'password' => bcrypt('password'),
            'uuid' => Str::uuid()->toString(),
        ])->save();

        $this->actingAs($user);

        $payload = [
            'name' => 'create_things',
            'description' => 'Permission to create things',
        ];

        $response = $this->postJson('/api/admin/permissions', $payload);

        $response->assertStatus(201);

        $this->assertDatabaseHas('permissions', [
            'name' => 'create_things',
            'description' => 'Permission to create things',
        ]);

        $created = Permission::where('name', 'create_things')->first();
        $this->assertNotNull($created);
        $this->assertEquals('create_things', $created->name);
    }
}