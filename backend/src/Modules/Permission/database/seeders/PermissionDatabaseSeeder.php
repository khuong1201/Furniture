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
        // Clear cache
        app()[\Illuminate\Cache\CacheManager::class]->forget('spatie.permission.cache');
        
        Schema::disableForeignKeyConstraints();
        
        // Truncate tables to ensure a clean slate
        $tables = ['permission_role', 'permissions', 'roles'];
        foreach ($tables as $table) {
            if (Schema::hasTable($table)) DB::table($table)->truncate();
        }

        Schema::enableForeignKeyConstraints();

        DB::transaction(function () {
            // Define Permissions by Module Group
            $permissionsByModule = [
                'System & Dashboard' => [
                    'dashboard.view',  
                    'log.view',        
                    'setting.view', 'setting.edit',
                ],
                'User & Auth' => [
                    'user.view', 'user.create', 'user.edit', 'user.delete',
                    'role.view', 'role.create', 'role.edit', 'role.delete',
                    'permission.view', 'permission.create',
                ],
                'Catalog (Products)' => [
                    'product.view', 'product.create', 'product.edit', 'product.delete',
                    'category.view', 'category.create', 'category.edit', 'category.delete',
                    'brand.view', 'brand.create', 'brand.edit', 'brand.delete',
                    'attribute.view', 'attribute.create', 'attribute.edit', 'attribute.delete',
                    'collection.view', 'collection.create', 'collection.edit', 'collection.delete',
                    'media.create', 'media.delete',
                ],
                'Sales (Orders)' => [
                    'order.view', 'order.view_all', 'order.create', 'order.edit', 'order.cancel',
                    'payment.view', 'payment.view_all', 'payment.edit',
                    'shipping.view', 'shipping.create', 'shipping.edit', 'shipping.delete',
                ],
                'Marketing' => [
                    'promotion.view', 'promotion.create', 'promotion.edit', 'promotion.delete',
                    'review.view_all', 'review.edit', 'review.delete',
                ],
                'Inventory' => [
                    'warehouse.view', 'warehouse.create', 'warehouse.edit', 'warehouse.delete',
                    'inventory.view', 'inventory.adjust', 'inventory.edit',
                ],
                'Customer Data' => [
                    'address.view', 'address.edit', 'address.delete',
                    'wishlist.view'
                ]
            ];

            $allPermissionIds = [];

            // 1. Create Permissions
            foreach ($permissionsByModule as $group => $perms) {
                foreach ($perms as $permName) {
                    $moduleKey = explode('.', $permName)[0];
                    
                    $permission = Permission::firstOrCreate(
                        ['name' => $permName],
                        [
                            'description' => "Access to $permName ($group)",
                            'module' => $moduleKey
                        ]
                    );
                    $allPermissionIds[] = $permission->id;
                }
            }

            // 2. Create Roles
            
            // ADMIN: Super User
            $adminRole = Role::firstOrCreate(
                ['name' => 'admin'], 
                ['slug' => 'admin', 'is_system' => true, 'description' => 'System Administrator with full access']
            );

            // MANAGER: Store/Business Manager
            $managerRole = Role::firstOrCreate(
                ['name' => 'manager'], 
                ['slug' => 'manager', 'is_system' => false, 'description' => 'Store Manager - Can manage operations but not system settings']
            );

            // STAFF: Sales/Support Staff
            $staffRole = Role::firstOrCreate(
                ['name' => 'staff'], 
                ['slug' => 'staff', 'is_system' => false, 'description' => 'Operational Staff - Limited access (No Delete)']
            );

            // CUSTOMER: End User
            $customerRole = Role::firstOrCreate(
                ['name' => 'customer'], 
                ['slug' => 'customer', 'is_system' => true, 'description' => 'Registered Customer']
            );

            // 3. Assign Permissions to Roles
            
            // ADMIN: Gets everything
            $adminRole->permissions()->sync($allPermissionIds);

            // MANAGER: Gets everything EXCEPT System/Logs/Roles/Permissions
            $managerPermissions = Permission::whereNotIn('module', ['log', 'role', 'permission', 'setting'])->pluck('id');
            $managerRole->permissions()->sync($managerPermissions);

            // STAFF: Sales & Inventory Operations only. NO DELETE PERMISSIONS.
            $staffPermissions = Permission::where(function($query) {
                $allowModules = ['order', 'product', 'inventory', 'shipping', 'attribute', 'collection', 'category', 'promotion', 'media'];
                $query->whereIn('module', $allowModules);
                $query->where('name', 'not like', '%delete%'); // Block delete actions
            })
            ->orWhere('name', 'dashboard.view') // Allow dashboard viewing
            ->pluck('id');
            
            $staffRole->permissions()->sync($staffPermissions);

            // CUSTOMER: End User Permissions (CHI TIẾT MỚI)
            $customerPermissions = Permission::whereIn('name', [
                // 1. Catalog Read-only (Xem hàng hóa)
                'product.view', 
                'category.view', 
                'brand.view', 
                'attribute.view', 
                'collection.view',
                
                // 2. Shopping Actions (Mua hàng)
                'order.create', // Đặt hàng
                'order.view',   // Xem đơn của mình
                'order.cancel', // Hủy đơn của mình
                'payment.view', // Xem lịch sử thanh toán
                'shipping.view',// Xem phương thức ship
                
                // 3. Marketing Interactions
                'promotion.view', // Xem khuyến mãi
                'review.view_all', // Xem đánh giá của người khác
                
                // 4. Personal Data (Dữ liệu cá nhân)
                'address.view', 
                'address.edit', 
                'address.delete',
                'wishlist.view'
            ])->pluck('id');

            $customerRole->permissions()->sync($customerPermissions);
        });
    }
}