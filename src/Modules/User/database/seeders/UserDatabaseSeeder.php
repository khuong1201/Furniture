<?php

namespace Modules\User\database\seeders;

use Illuminate\Database\Seeder;
use Modules\User\Models\User;

class UserDatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::factory()->count(10)->create();

        $adminRole = Role::firstOrCreate(['name' => 'admin']);

        $permissions = ['user.view', 'user.create', 'user.edit', 'user.delete'];
        foreach ($permissions as $perm) {
            Permission::firstOrCreate(['name' => $perm]);
        }

        $adminRole->permissions()->sync(Permission::whereIn('name', $permissions)->pluck('id'));

        $admin = User::create([
            'uuid' => \Str::uuid(),
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'password' => bcrypt('admin123'),
            'is_active' => true,
            'is_deleted' => false,
        ]);

        $admin->roles()->sync([$adminRole->id]);
        }
}
