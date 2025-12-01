<?php

namespace Modules\Role\database\seeders;

use Illuminate\Database\Seeder;
use Modules\Role\Domain\Models\Role;
use Modules\Permission\Domain\Models\Permission;
use Illuminate\Support\Facades\DB;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function () {
            $permissions = [
                'user.view', 'user.create', 'user.edit', 'user.delete',
                'product.view', 'product.create', 'product.edit', 'product.delete',
                'order.view', 'order.edit', 
                'inventory.view', 'inventory.adjust',
                'report.view'
            ];

            foreach ($permissions as $perm) {
                Permission::firstOrCreate(['name' => $perm], [
                    'description' => "Quyền truy cập $perm",
                    'module' => explode('.', $perm)[0]
                ]);
            }

            $adminRole = Role::firstOrCreate(['name' => 'admin'], ['is_system' => true, 'description' => 'Quản trị viên hệ thống']);
            $staffRole = Role::firstOrCreate(['name' => 'staff'], ['is_system' => false, 'description' => 'Nhân viên vận hành']);
            $customerRole = Role::firstOrCreate(['name' => 'customer'], ['is_system' => true, 'description' => 'Khách hàng']);

            $allPermissions = Permission::all();
            $adminRole->permissions()->sync($allPermissions);

            $staffPerms = Permission::where('name', 'like', 'product.%')
                ->orWhere('name', 'like', 'order.%')
                ->orWhere('name', 'like', 'inventory.%')
                ->get();
            $staffRole->permissions()->sync($staffPerms);
        });
    }
}