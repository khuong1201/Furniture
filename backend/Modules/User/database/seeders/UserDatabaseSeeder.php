<?php

namespace Modules\User\database\seeders;

use Illuminate\Database\Seeder;
use Modules\User\Domain\Models\User;
use Illuminate\Support\Facades\Hash;
use Modules\Role\Domain\Models\Role;

class UserDatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Admin
        $admin = User::firstOrCreate(['email' => 'admin@system.com'], [
            'name' => 'System Admin',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
            'is_active' => true,
        ]);
        $this->assignRole($admin, 'admin');

        // 2. Manager
        $manager = User::firstOrCreate(['email' => 'manager@system.com'], [
            'name' => 'Store Manager',
            'password' => Hash::make('password'),
            'is_active' => true,
        ]);
        $this->assignRole($manager, 'manager');

        // 3. Staff
        $staff = User::firstOrCreate(['email' => 'staff@system.com'], [
            'name' => 'Sales Staff',
            'password' => Hash::make('password'),
            'is_active' => true,
        ]);
        $this->assignRole($staff, 'staff');

        // 4. Customer
        $customer = User::firstOrCreate(['email' => 'customer@gmail.com'], [
            'name' => 'John Doe',
            'password' => Hash::make('password'),
            'is_active' => true,
        ]);
        $this->assignRole($customer, 'customer');
    }

    private function assignRole(User $user, string $roleName): void
    {
        $role = Role::where('name', $roleName)->first();
        if ($role) $user->roles()->syncWithoutDetaching([$role->id]);
    }
}