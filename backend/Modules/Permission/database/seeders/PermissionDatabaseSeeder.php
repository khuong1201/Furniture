<?php

namespace Modules\Permission\database\seeders;

use Illuminate\Database\Seeder;
use Modules\Role\Domain\Models\Role;
use Modules\Permission\Domain\Models\Permission;

class PermissionDatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $permissions = [
            // Users
            ['name' => 'user.index', 'description' => 'View user list'],
            ['name' => 'user.store', 'description' => 'Create new user'],
            ['name' => 'user.show', 'description' => 'View user details'],
            ['name' => 'user.update', 'description' => 'Update user'],
            ['name' => 'user.destroy', 'description' => 'Delete user'],

            // Orders
            ['name' => 'order.index', 'description' => 'View order list'],
            ['name' => 'order.store', 'description' => 'Create new order'],
            ['name' => 'order.show', 'description' => 'View order details'],
            ['name' => 'order.update', 'description' => 'Update order'],
            ['name' => 'order.destroy', 'description' => 'Delete order'],

            // Products
            ['name' => 'product.index', 'description' => 'View product list'],
            ['name' => 'product.store', 'description' => 'Create new product'],
            ['name' => 'product.show', 'description' => 'View product details'],
            ['name' => 'product.update', 'description' => 'Update product'],
            ['name' => 'product.destroy', 'description' => 'Delete product'],

            // Product Images
            ['name' => 'product_image.store', 'description' => 'Upload product images'],
            ['name' => 'product_image.destroy', 'description' => 'Delete product images'],

            // Permissions
            ['name' => 'permission.index', 'description' => 'View permission list'],
            ['name' => 'permission.store', 'description' => 'Create new permission'],
            ['name' => 'permission.show', 'description' => 'View permission details'],
            ['name' => 'permission.update', 'description' => 'Update permission'],
            ['name' => 'permission.destroy', 'description' => 'Delete permission'],

            // Roles
            ['name' => 'role.index', 'description' => 'View role list'],
            ['name' => 'role.store', 'description' => 'Create new role'],
            ['name' => 'role.show', 'description' => 'View role details'],
            ['name' => 'role.update', 'description' => 'Update role'],
            ['name' => 'role.destroy', 'description' => 'Delete role'],

            // Categories
            ['name' => 'category.index', 'description' => 'View category list'],
            ['name' => 'category.store', 'description' => 'Create new category'],
            ['name' => 'category.show', 'description' => 'View category details'],
            ['name' => 'category.update', 'description' => 'Update category'],
            ['name' => 'category.destroy', 'description' => 'Delete category'],

            // Inventories
            ['name' => 'inventory.index', 'description' => 'View inventory list'],
            ['name' => 'inventory.store', 'description' => 'Create or update inventory'],
            ['name' => 'inventory.adjust', 'description' => 'Adjust stock quantity'],
            ['name' => 'inventory.show', 'description' => 'View inventory details'],
            ['name' => 'inventory.destroy', 'description' => 'Delete inventory'],

            // Payments
            ['name' => 'payment.index', 'description' => 'View payment list'],
            ['name' => 'payment.store', 'description' => 'Create new payment'],
            ['name' => 'payment.update', 'description' => 'Update payment'],
            ['name' => 'payment.destroy', 'description' => 'Delete payment'],

            // Reviews
            ['name' => 'review.index', 'description' => 'View review list'],
            ['name' => 'review.store', 'description' => 'Create new review'],
            ['name' => 'review.update', 'description' => 'Update review'],
            ['name' => 'review.destroy', 'description' => 'Delete review'],

            // Notifications
            ['name' => 'notification.index', 'description' => 'View notifications'],
            ['name' => 'notification.store', 'description' => 'Create notification'],
            ['name' => 'notification.update', 'description' => 'Update notification'],
            ['name' => 'notification.destroy', 'description' => 'Delete notification'],

            // Shippings
            ['name' => 'shipping.index', 'description' => 'View shipping list'],
            ['name' => 'shipping.store', 'description' => 'Create new shipping'],
            ['name' => 'shipping.update', 'description' => 'Update shipping'],
            ['name' => 'shipping.destroy', 'description' => 'Delete shipping'],

            // Warehouses
            ['name' => 'warehouse.index', 'description' => 'View warehouse list'],
            ['name' => 'warehouse.store', 'description' => 'Create new warehouse'],
            ['name' => 'warehouse.show', 'description' => 'View warehouse details'],
            ['name' => 'warehouse.update', 'description' => 'Update warehouse'],
            ['name' => 'warehouse.destroy', 'description' => 'Delete warehouse'],

            // Auth (for admin panel management)
            ['name' => 'auth.login', 'description' => 'Login to system'],
            ['name' => 'auth.logout', 'description' => 'Logout from system'],
            ['name' => 'auth.register', 'description' => 'Register new user'],
            ['name' => 'auth.refresh', 'description' => 'Refresh access token'],
        ];

        foreach ($permissions as $perm) {
            Permission::updateOrCreate(
                ['name' => $perm['name']],
                ['description' => $perm['description']]
            );
        }

        $admin = Role::firstOrCreate(['name' => 'admin']);
        $user  = Role::firstOrCreate(['name' => 'user']);

        $allPermissions = Permission::pluck('id')->toArray();
        $admin->permissions()->sync($allPermissions);

        $userPermissions = Permission::whereIn('name', [
            // Profile
            'user.show',
            'user.update',

            // Product browsing
            'product.index',
            'product.show',

            // Categories
            'category.index',
            'category.show',

            // Reviews (self)
            'review.index',
            'review.store',
            'review.update',
            'review.destroy',

            // Orders (self)
            'order.store',
            'order.index',
            'order.show',
            'order.update',

            // Payments (self)
            'payment.store',
            'payment.index',
            'payment.update',

            // // Notifications
            // 'notification.index',
            // 'notification.show',
            // 'notification.update',

            // Shipping
            'shipping.index',
            'shipping.show',
        ])->pluck('id')->toArray();

        $user->permissions()->sync($userPermissions);
    }
}
