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
        
        $tables = ['permission_role', 'role_has_permissions', 'role_user', 'model_has_roles', 'permissions', 'roles'];
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
                'Catalog (Sản phẩm)' => [
                    'product.view',  
                    'product.create',   
                    'product.edit',    
                    'product.delete',   
                    
                    'category.create', 'category.edit', 'category.delete', 
                    
                    'attribute.view',   
                    'attribute.create', 
                    'attribute.edit', 
                    'attribute.delete',
                    
                    'collection.create', 'collection.edit', 'collection.delete',
                ],
                'Sales (Bán hàng)' => [
                    'order.view',      
                    'order.edit',       
                    'order.cancel',    
                    
                    'payment.view',    
                    'payment.edit',  
                    
                    'shipping.view',   
                    'shipping.create', 
                    'shipping.edit',  
                ],
                'Marketing' => [
                    'promotion.view', 'promotion.create', 'promotion.edit', 'promotion.delete',
                    'review.edit',     
                    'review.delete',    
                ],
                'Inventory (Kho)' => [
                    'warehouse.view',   'warehouse.create', 'warehouse.edit', 'warehouse.delete',
                    'inventory.view',  
                    'inventory.adjust',
                    'inventory.edit',   
                ],
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

            $adminRole = Role::create(['name' => 'admin', 'is_system' => true, 'description' => 'Super Admin']);
            $managerRole = Role::create(['name' => 'manager', 'is_system' => false, 'description' => 'Store Manager']);
            $staffRole = Role::create(['name' => 'staff', 'is_system' => false, 'description' => 'Operational Staff']);
            $customerRole = Role::create(['name' => 'customer', 'is_system' => true, 'description' => 'End User']);

            $adminRole->permissions()->sync(collect($allPermissions)->pluck('id'));

            $managerPermissions = collect($allPermissions)->filter(function ($perm) {

                return !in_array($perm->module, ['log', 'role', 'setting']);
            });
            $managerRole->permissions()->sync($managerPermissions->pluck('id'));

            $staffPermissions = collect($allPermissions)->filter(function ($perm) {

                $allowModules = ['order', 'product', 'inventory', 'shipping', 'attribute', 'collection', 'category', 'promotion'];

                $isSafeAction = !str_contains($perm->name, 'delete');
                
                return in_array($perm->module, $allowModules) && $isSafeAction;
            });

            $dashboardView = Permission::where('name', 'dashboard.view')->first();
            if ($dashboardView) $staffPermissions->push($dashboardView);

            $staffRole->permissions()->sync($staffPermissions->pluck('id'));

        });
    }
}