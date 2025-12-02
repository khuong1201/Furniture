<?php

namespace Modules\Role\database\seeders;

use Illuminate\Database\Seeder;
use Modules\Role\Domain\Models\Role;
use Modules\Permission\Domain\Models\Permission;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        Schema::disableForeignKeyConstraints();
        DB::table('permission_role')->truncate();
        DB::table('permissions')->truncate();
        DB::table('roles')->truncate();
        Schema::enableForeignKeyConstraints();

        DB::transaction(function () {
            $permissionsByModule = [
                'System' => [
                    'dashboard.view', 
                    'log.view',       
                    'setting.view',    
                    'setting.edit',     
                ],
                'User & Auth' => [
                    'user.view',    
                    'user.create',      
                    'user.edit',     
                    'user.delete',     
                    'role.view',       
                    'role.create',      'role.edit', 'role.delete',
                ],
                'Catalog (Sản phẩm)' => [
                    'product.view',     
                    'product.create',   'product.edit', 'product.delete',
                    'category.create',  'category.edit', 'category.delete',
                    'collection.create','collection.edit','collection.delete', 
                ],
                'Sales (Bán hàng)' => [
                    'order.view',   
                    'order.edit',      
                    'order.cancel',    
                    'payment.view', 
                    'payment.edit',    
                    'shipping.view',    'shipping.create', 'shipping.edit', 
                ],
                'Marketing' => [
                    'promotion.view',   'promotion.create', 'promotion.edit', 'promotion.delete',
                    'review.edit',      
                    'review.delete',    
                ],
                'Inventory (Kho)' => [
                    'warehouse.view',   'warehouse.create', 'warehouse.edit', 'warehouse.delete',
                    'inventory.view',   
                    'inventory.adjust',
                ],

            ];

            $allPermissions = [];
            foreach ($permissionsByModule as $module => $perms) {
                foreach ($perms as $permName) {
                    $permission = Permission::create([
                        'name' => $permName,
                        'description' => "Quyền $permName thuộc module $module",
                        'module' => explode('.', $permName)[0] 
                    ]);
                    $allPermissions[] = $permission;
                }
            }

            $adminRole = Role::create([
                'name' => 'admin',
                'description' => 'Quản trị viên cấp cao (Super Admin)',
                'is_system' => true
            ]);

            $managerRole = Role::create([
                'name' => 'manager',
                'description' => 'Quản lý cửa hàng (Không can thiệp hệ thống)',
                'is_system' => false
            ]);

            $staffRole = Role::create([
                'name' => 'staff',
                'description' => 'Nhân viên vận hành (Sales/Kho)',
                'is_system' => false
            ]);

            $customerRole = Role::create([
                'name' => 'customer',
                'description' => 'Khách hàng mua sắm',
                'is_system' => true
            ]);

            $adminRole->permissions()->sync(collect($allPermissions)->pluck('id'));

            $managerPermissions = collect($allPermissions)->filter(function ($perm) {
                return !in_array($perm->module, ['log', 'setting', 'role', 'permission']);
            });
            $managerRole->permissions()->sync($managerPermissions->pluck('id'));

            $staffPermissions = collect($allPermissions)->filter(function ($perm) {
                $allowModules = ['order', 'product', 'inventory', 'shipping'];
                $isSafeAction = !str_contains($perm->name, 'delete');
                
                return in_array($perm->module, $allowModules) && $isSafeAction;
            });
            $warehouseView = Permission::where('name', 'warehouse.view')->first();
            if ($warehouseView) $staffPermissions->push($warehouseView);

            $staffRole->permissions()->sync($staffPermissions->pluck('id'));
        });
    }
}