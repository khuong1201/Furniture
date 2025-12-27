<?php

namespace Modules\User\database\seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Modules\User\Domain\Models\User;
use Modules\Role\Domain\Models\Role;
use Illuminate\Support\Str;

class UserDatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $password = Hash::make('password');
        $now = now();

        $adminRole = Role::firstOrCreate(
            ['name' => 'admin'], 
            ['guard_name' => 'api', 'slug' => 'admin']
        );

        $custRole = Role::firstOrCreate(
            ['name' => 'customer'],
            ['guard_name' => 'api', 'slug' => 'customer']
        );
        $admin = User::updateOrCreate(
            ['email' => 'admin@system.com'], 
            [
                'uuid'              => (string) Str::uuid(),
                'name'              => 'Super Admin',
                'password'          => $password,
                'is_active'         => true,
                'email_verified_at' => $now,
            ]
        );
        $admin->roles()->syncWithoutDetaching([$adminRole->id]);

        // 3. Tạo Khách hàng mẫu
        $customer = User::updateOrCreate(
            ['email' => 'customer@gmail.com'],
            [
                'uuid'              => (string) Str::uuid(),
                'name'              => 'Nguyen Van A',
                'password'          => $password,
                'is_active'         => true,
                'email_verified_at' => $now,
            ]
        );
        $customer->roles()->syncWithoutDetaching([$custRole->id]);

        // 4. Tạo 20 Khách hàng ngẫu nhiên (Factory)
        if ($custRole) {
            User::factory()
                ->count(20)
                ->create()
                ->each(function ($user) use ($custRole) {
                    $user->roles()->attach($custRole->id);
                });
        }
    }
}