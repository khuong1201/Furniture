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
        app()[\Illuminate\Cache\CacheManager::class]->forget('spatie.permission.cache');
        
        Schema::disableForeignKeyConstraints();
        Permission::truncate();
        Role::truncate();
        DB::table('role_user')->truncate();
        DB::table('permission_role')->truncate();
        Schema::enableForeignKeyConstraints();

        DB::transaction(function () {
            $permissionsMap = [
                'user' => ['index', 'store', 'show', 'update', 'destroy'],
                'role' => ['index', 'store', 'show', 'update', 'destroy'],
                'permission' => ['index', 'store'],
                'product' => ['index', 'store', 'show', 'update', 'destroy'],
                'category' => ['index', 'store', 'show', 'update', 'destroy'],
                'order' => ['index', 'store', 'show', 'update', 'destroy', 'cancel'],
                'inventory' => ['index', 'adjust'],
                'report' => ['view'],
                'log' => ['view'],
            ];

            $allPermissionIds = [];

            foreach ($permissionsMap as $module => $actions) {
                foreach ($actions as $action) {
                    $name = "{$module}.{$action}";
                    $permission = Permission::create([
                        'name' => $name,
                        'module' => $module,
                        'description' => ucfirst($action) . " {$module}"
                    ]);
                    $allPermissionIds[] = $permission->id;
                }
            }

            // Create Roles
            $adminRole = Role::create([
                'name' => 'admin', 
                'slug' => 'admin',
                'is_system' => true, 
                'description' => 'Super Admin'
            ]);
            
            $managerRole = Role::create([
                'name' => 'manager', 
                'slug' => 'manager',
                'is_system' => false, 
                'description' => 'Store Manager'
            ]);

            $customerRole = Role::create([
                'name' => 'customer', 
                'slug' => 'customer',
                'is_system' => true, 
                'description' => 'Customer'
            ]);

            $adminRole->permissions()->sync($allPermissionIds);

            $managerPermissions = Permission::whereNotIn('module', ['role', 'permission', 'log'])->pluck('id');
            $managerRole->permissions()->sync($managerPermissions);

            $customerPermissions = Permission::whereIn('name', [
                'product.index', 'product.show', 'category.index', 'order.store', 'order.show'
            ])->pluck('id');
            $customerRole->permissions()->sync($customerPermissions);
        });
    }
}