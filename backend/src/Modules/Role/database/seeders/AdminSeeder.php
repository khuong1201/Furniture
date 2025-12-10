<?php

namespace Modules\Role\database\seeders;

use Illuminate\Database\Seeder;
use Modules\User\Domain\Models\User;
use Modules\Role\Domain\Models\Role;
use Illuminate\Support\Str;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Ensure admin role exists
        $adminRole = Role::firstOrCreate(
            ['name' => 'admin'],
            ['description' => 'Super Admin', 'is_system' => true]
        );

        // 2. Ensure admin role has ALL permissions
        // This is a safety check in case RolePermissionSeeder didn't sync correctly
        $allPermissions = \Modules\Permission\Domain\Models\Permission::all();
        if ($allPermissions->count() > 0) {
            $adminRole->permissions()->syncWithoutDetaching($allPermissions->pluck('id'));
        }

        // 3. Create/Update default admin user (admin@system.com)
        $systemAdmin = User::firstOrCreate(
            ['email' => 'admin@system.com'],
            [
                'uuid' => (string) Str::uuid(),
                'name' => 'Super Admin',
                'password' => bcrypt('123456'),
                'is_active' => true,
            ]
        );
        $systemAdmin->roles()->syncWithoutDetaching([$adminRole->id]);

        // 4. Fix permissions for current user (admin@gmail.com) if exists
        $currentAdmin = User::where('email', 'admin@gmail.com')->first();
        if ($currentAdmin) {
            $currentAdmin->roles()->syncWithoutDetaching([$adminRole->id]);
            $this->command->info("Granted admin role to admin@gmail.com");
        }

        $this->command->info("Admin initialized. Login: admin@system.com / 123456");
    }
}
