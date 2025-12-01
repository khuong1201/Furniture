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
                'role.view', 'role.create', 'role.edit', 'role.delete',
                'permission.view', 'permission.create', 'permission.edit', 'permission.delete',
                
                'log.view', 
                
                'category.view', 'category.create', 'category.edit', 'category.delete',
                'product.view', 'product.create', 'product.edit', 'product.delete',
                'review.view', 'review.create', 'review.edit', 'review.delete', 
                
                'warehouse.view', 'warehouse.create', 'warehouse.edit', 'warehouse.delete',
                'inventory.view', 'inventory.adjust',
                
                'order.view', 'order.create', 'order.edit', 'order.delete', 
                'cart.view', 'cart.delete', 
                'payment.view', 'payment.create', 'payment.edit',
                'shipping.view', 'shipping.create', 'shipping.edit', 'shipping.delete',

                'promotion.view', 'promotion.create', 'promotion.edit', 'promotion.delete',

                'address.view', 'address.create', 'address.edit', 'address.delete',
                'notification.view', 'notification.create', 'notification.delete',
            ];

            foreach ($permissions as $perm) {
                Permission::firstOrCreate(['name' => $perm], [
                    'description' => "Quyền thao tác $perm",
                    'module' => explode('.', $perm)[0] 
                ]);
            }

            $adminRole = Role::firstOrCreate(['name' => 'admin'], ['is_system' => true, 'description' => 'Quản trị viên hệ thống']);
            $staffRole = Role::firstOrCreate(['name' => 'staff'], ['is_system' => false, 'description' => 'Nhân viên vận hành']);
            $customerRole = Role::firstOrCreate(['name' => 'customer'], ['is_system' => true, 'description' => 'Khách hàng']);

            $allPermissions = Permission::all();
            $adminRole->permissions()->sync($allPermissions);

            $staffExcludedModules = ['user', 'role', 'permission', 'log'];
            
            $staffPerms = $allPermissions->filter(function ($permission) use ($staffExcludedModules) {
                $moduleName = explode('.', $permission->name)[0];
                return !in_array($moduleName, $staffExcludedModules);
            });
            
            $staffRole->permissions()->sync($staffPerms);

            // 6. Gán quyền cho Customer (Tùy chọn)
            // Thường Customer không cần gán permission cứng trong DB nếu dùng Policy check user_id
            // Nhưng nếu cần, có thể gán các quyền cơ bản:
            // $customerPerms = Permission::whereIn('name', ['product.view', 'category.view', 'review.create'])->get();
            // $customerRole->permissions()->sync($customerPerms);
        });
    }
}