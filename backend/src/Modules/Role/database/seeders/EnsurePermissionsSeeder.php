<?php

namespace Modules\Role\database\seeders;

use Illuminate\Database\Seeder;
use Modules\Permission\Domain\Models\Permission;
use Modules\Role\Domain\Models\Role;
use Modules\User\Domain\Models\User;

class EnsurePermissionsSeeder extends Seeder
{
    public function run(): void
    {
        $permissions = [
            // Product
            ['name' => 'product.view', 'module' => 'product'],
            ['name' => 'product.create', 'module' => 'product'],
            ['name' => 'product.edit', 'module' => 'product'],
            ['name' => 'product.delete', 'module' => 'product'],

            // Category
            ['name' => 'category.create', 'module' => 'category'],
            ['name' => 'category.edit', 'module' => 'category'],
            ['name' => 'category.delete', 'module' => 'category'],

            // Order
            ['name' => 'order.view', 'module' => 'order'],
            ['name' => 'order.edit', 'module' => 'order'],

            // User
            ['name' => 'user.view', 'module' => 'user'],
            ['name' => 'user.create', 'module' => 'user'],
            ['name' => 'user.edit', 'module' => 'user'],
            ['name' => 'user.delete', 'module' => 'user'],

            // Inventory
            ['name' => 'inventory.view', 'module' => 'inventory'],
            ['name' => 'inventory.adjust', 'module' => 'inventory'],
            ['name' => 'inventory.create', 'module' => 'inventory'],

            // Warehouse
            ['name' => 'warehouse.view', 'module' => 'warehouse'],
            ['name' => 'warehouse.create', 'module' => 'warehouse'],
            ['name' => 'warehouse.update', 'module' => 'warehouse'],
            ['name' => 'warehouse.delete', 'module' => 'warehouse'],
        ];

        $permissionIds = [];

        foreach ($permissions as $p) {
            $perm = Permission::firstOrCreate(
                ['name' => $p['name']],
                ['module' => $p['module'], 'description' => "Permission " . $p['name']]
            );
            $permissionIds[] = $perm->id;
            $this->command->info("Ensured permission: " . $p['name']);
        }

        // Ensure Admin Role
        $adminRole = Role::firstOrCreate(['name' => 'admin'], ['description' => 'Super Admin', 'is_system' => true]);

        // Sync Permissions
        $adminRole->permissions()->syncWithoutDetaching($permissionIds);
        $this->command->info("Synced all permissions to Admin role.");

        // Ensure Users have Admin Role
        $emails = ['admin@system.com', 'admin@gmail.com'];
        foreach ($emails as $email) {
            $user = User::where('email', $email)->first();
            if ($user) {
                $user->roles()->syncWithoutDetaching([$adminRole->id]);
                $this->command->info("Assigned Admin role to: $email");
            }
        }
    }
}
