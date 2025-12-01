<?php

namespace Modules\Permission\database\seeders;

use Illuminate\Database\Seeder;
use Modules\Role\Domain\Models\Role;
use Modules\Permission\Domain\Models\Permission;
use Illuminate\Support\Facades\DB;

class PermissionDatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::transaction(function () {
            // 1. Business Logic Permissions List
            $permissions = [
                ['name' => 'user.index', 'description' => 'View user list', 'module' => 'user'],
                ['name' => 'user.store', 'description' => 'Create new user', 'module' => 'user'],
                ['name' => 'user.show', 'description' => 'View user details', 'module' => 'user'],
                ['name' => 'user.update', 'description' => 'Update user information', 'module' => 'user'],
                ['name' => 'user.destroy', 'description' => 'Delete user', 'module' => 'user'],

                ['name' => 'role.manage', 'description' => 'Manage roles', 'module' => 'role'],
                ['name' => 'permission.manage', 'description' => 'Manage permissions', 'module' => 'permission'],

                ['name' => 'product.index', 'description' => 'View product list', 'module' => 'product'],
                ['name' => 'product.store', 'description' => 'Create new product', 'module' => 'product'],
                ['name' => 'product.show', 'description' => 'View product details', 'module' => 'product'],
                ['name' => 'product.update', 'description' => 'Update product', 'module' => 'product'],
                ['name' => 'product.destroy', 'description' => 'Delete product', 'module' => 'product'],

                ['name' => 'category.index', 'description' => 'View categories', 'module' => 'category'],
                ['name' => 'category.manage', 'description' => 'Manage categories (Create/Edit/Delete)', 'module' => 'category'],

                ['name' => 'order.index', 'description' => 'View order list', 'module' => 'order'],
                ['name' => 'order.store', 'description' => 'Create order (Checkout)', 'module' => 'order'],
                ['name' => 'order.show', 'description' => 'View order details', 'module' => 'order'],
                ['name' => 'order.update', 'description' => 'Update order status', 'module' => 'order'],
                ['name' => 'order.destroy', 'description' => 'Cancel/Delete order', 'module' => 'order'],

                ['name' => 'inventory.index', 'description' => 'View inventory', 'module' => 'inventory'],
                ['name' => 'inventory.adjust', 'description' => 'Adjust inventory (Import/Export/Balance)', 'module' => 'inventory'],
                ['name' => 'warehouse.manage', 'description' => 'Manage warehouses', 'module' => 'warehouse'],

                ['name' => 'review.manage', 'description' => 'Manage reviews (Approve/Delete)', 'module' => 'review'],
                ['name' => 'review.create', 'description' => 'Write review', 'module' => 'review'],

                ['name' => 'report.view', 'description' => 'View revenue report', 'module' => 'report'],
            ];

            // 2. Create Permissions
            foreach ($permissions as $perm) {
                Permission::updateOrCreate(
                    ['name' => $perm['name']],
                    [
                        'description' => $perm['description'],
                        'module' => $perm['module']
                    ]
                );
            }

            $adminRole = Role::firstOrCreate(
                ['name' => 'admin'],
                ['description' => 'System Administrator (Super Admin)', 'is_system' => true]
            );
            $allPermissionIds = Permission::pluck('id')->toArray();
            $adminRole->permissions()->sync($allPermissionIds);

            $customerRole = Role::firstOrCreate(
                ['name' => 'customer'],
                ['description' => 'Shopping Customer', 'is_system' => true]
            );

            $customerPermissions = Permission::whereIn('name', [
                'product.index',
                'product.show',
                'category.index',
                'order.store',
                'order.show',  
                'review.create'
            ])->pluck('id')->toArray();

            $customerRole->permissions()->sync($customerPermissions);
        });
    }
}