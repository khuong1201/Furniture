<?php

namespace Modules\Permission\database\seeders;

use Illuminate\Database\Seeder;
use Modules\Role\Domain\Models\Role;
use Modules\Permission\Domain\Models\Permission;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class PermissionDatabaseSeeder extends Seeder
{
    public function run(): void
    {
        Schema::disableForeignKeyConstraints();
        
        // Truncate tables to avoid duplicates
        $tables = ['permission_role', 'permissions', 'roles'];
        foreach ($tables as $table) {
            if (Schema::hasTable($table)) DB::table($table)->truncate();
        }

        Schema::enableForeignKeyConstraints();

        DB::transaction(function () {
            $permissionsByModule = [
                'System & Dashboard' => [
                    'dashboard.view',
                    'log.view',        
                    'setting.view',     
                    'setting.edit',   
                ],
                'User & Auth' => [
                    'user.view', 'user.create', 'user.edit', 'user.delete',
                    'role.view', 'role.create', 'role.edit', 'role.delete',
                ],
                'Catalog' => [
                    'product.view', 'product.create', 'product.edit', 'product.delete',
                    'category.create', 'category.edit', 'category.delete', 
                    'attribute.view', 'attribute.create', 'attribute.edit', 'attribute.delete',
                    'collection.create', 'collection.edit', 'collection.delete',
                    'media.create', 'media.delete' 
                ],
                'Sales' => [
                    'order.view', 'order.view_all', 'order.edit', 'order.cancel', "order.buynow",
                    'payment.view', 'payment.edit', 'payment.view_all',
                    'shipping.view', 'shipping.create', 'shipping.edit',
                ],
                'Marketing' => [
                    'promotion.view', 'promotion.create', 'promotion.edit', 'promotion.delete',
                    'review.view_all', 'review.edit', 'review.delete',
                ],
                'Inventory' => [
                    'warehouse.view', 'warehouse.create', 'warehouse.edit', 'warehouse.delete',
                    'inventory.view', 'inventory.adjust', 'inventory.edit',
                ],
                'Customer' => [
                    'address.view', 'address.edit', 'address.delete', 
                    'wishlist.view'
                ]
            ];

            $allPermissions = [];
            foreach ($permissionsByModule as $module => $perms) {
                foreach ($perms as $permName) {
                    $moduleKey = explode('.', $permName)[0];
                    $permission = Permission::create([
                        'name' => $permName,
                        'description' => "Quyền $permName ($module)",
                        'module' => $moduleKey
                    ]);
                    $allPermissions[] = $permission;
                }
            }

            // 1. ADMIN ROLE (Full Permissions)
            $adminRole = Role::create(['name' => 'admin', 'is_system' => true, 'description' => 'Super Admin']);
            // Sync tất cả permission cho Admin (bao gồm dashboard.view)
            $adminRole->permissions()->sync(collect($allPermissions)->pluck('id'));

            // 2. MANAGER ROLE
            $managerRole = Role::create(['name' => 'manager', 'is_system' => false, 'description' => 'Store Manager']);
            $managerPermissions = collect($allPermissions)->filter(function ($perm) {
                return !in_array($perm->module, ['log', 'role', 'setting']);
            });
            $managerRole->permissions()->sync($managerPermissions->pluck('id'));

            // 3. STAFF ROLE
            $staffRole = Role::create(['name' => 'staff', 'is_system' => false, 'description' => 'Operational Staff']);
            $staffPermissions = collect($allPermissions)->filter(function ($perm) {
                $allowModules = ['order', 'product', 'inventory', 'shipping', 'attribute', 'collection', 'category', 'promotion'];
                $isSafeAction = !str_contains($perm->name, 'delete');
                // Cho phép Staff xem dashboard
                if ($perm->name === 'dashboard.view') return true;
                
                return in_array($perm->module, $allowModules) && $isSafeAction;
            });
            $staffRole->permissions()->sync($staffPermissions->pluck('id'));

            // 4. CUSTOMER ROLE (Quyền hạn chế)
            $customerRole = Role::create(['name' => 'customer', 'is_system' => true, 'description' => 'End User']);
            // Customer thường không cần permission trong bảng này (vì họ dùng public API), 
            // nhưng nếu cần phân quyền sâu hơn thì gán ở đây.
        });
    }
}