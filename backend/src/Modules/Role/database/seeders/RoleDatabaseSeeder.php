<?php

namespace Modules\Role\database\seeders;

use Illuminate\Database\Seeder;
use Modules\Role\Domain\Models\Role;
use Modules\Permission\Domain\Models\Permission;

class RoleDatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $admin = Role::firstOrCreate(['name' => 'admin', 'slug' => 'admin'], ['is_system' => true]);
        $staff = Role::firstOrCreate(['name' => 'staff', 'slug' => 'staff'], ['is_system' => false]);
        $customer = Role::firstOrCreate(['name' => 'customer', 'slug' => 'customer'], ['is_system' => true]);

        $permissions = Permission::all();
        $admin->permissions()->sync($permissions);

        $staffPerms = Permission::whereIn('module', ['order', 'product', 'inventory'])->get();
        $staff->permissions()->sync($staffPerms);
    }
}