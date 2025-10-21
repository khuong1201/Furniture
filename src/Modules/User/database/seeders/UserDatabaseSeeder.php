<?php

namespace Modules\User\database\seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use Modules\User\Models\User;
use Modules\Role\Models\Role;
use Modules\Permission\Models\Permission;

class UserDatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $customerRole = Role::firstOrCreate(['name' => 'customer'],);
        // Tạo sẵn user giả (10 user random)
        User::factory()->count(10)->create()->each(function($user) use ($customerRole) {
            if (method_exists($user, 'roles')) {
                $user->roles()->attach($customerRole->id);
            }
        });

        // ====== Tạo role Admin ======
        $adminRole = Role::firstOrCreate(['name' => 'admin']);

        // ====== Tạo danh sách quyền ======
        $permissions = ['user.view', 'user.create', 'user.edit', 'user.delete'];

        foreach ($permissions as $perm) {
            Permission::firstOrCreate(['name' => $perm]);
        }

        // ====== Gán các quyền cho vai trò admin ======
        $adminRole->permissions()->sync(
            Permission::whereIn('name', $permissions)->pluck('id')->toArray()
        );

        // ====== Tạo tài khoản admin ======
        $admin = User::updateOrCreate(
            ['email' => 'admin@example.com'],
            [
                'uuid' => Str::uuid(),
                'name' => 'Admin User',
                'password' => bcrypt('admin123'),
                'email_verified_at' => now(),
                'is_active' => true,
                'is_deleted' => false,
            ]
        );

        // ====== Gán role admin cho user ======
        if (method_exists($admin, 'roles')) {
            $admin->roles()->sync([$adminRole->id]);
        }
        
    }
}
