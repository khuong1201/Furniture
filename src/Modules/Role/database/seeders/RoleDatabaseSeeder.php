<?php

namespace Modules\Role\database\seeders;
use Modules\Permission\Models\Permission;
use Modules\User\Models\User;
use Modules\Role\Models\Role;
use Illuminate\Database\Seeder;

class RoleDatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $admin = Role::firstOrCreate(['name' => 'admin'], ['label' => 'Quản trị viên']);

        $permissions = [
            ['name' => 'user.view', 'label' => 'Xem người dùng'],
            ['name' => 'user.create', 'label' => 'Tạo người dùng'],
            ['name' => 'user.edit', 'label' => 'Sửa người dùng'],
            ['name' => 'user.delete', 'label' => 'Xóa người dùng'],
        ];

        foreach ($permissions as $perm) {
            $permission = Permission::firstOrCreate(['name' => $perm['name']], ['label' => $perm['label']]);
            $admin->permissions()->syncWithoutDetaching($permission->id);
        }

        $user = User::first();
        if ($user) {
            $user->roles()->syncWithoutDetaching($admin->id);
        }
    }
}
