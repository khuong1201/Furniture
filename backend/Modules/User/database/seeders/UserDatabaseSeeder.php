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
        // 1. Admin User
        $admin = User::firstOrCreate(
            ['email' => 'admin@system.com'],
            [
                'name' => 'System Admin',
                'password' => Hash::make('password'), // Default password
                'email_verified_at' => now(),
                'is_active' => true,
            ]
        );
        // Gán Role Admin (Giả định Role đã được tạo ở PermissionSeeder)
        $adminRole = Role::where('name', 'admin')->first();
        if ($adminRole) $admin->roles()->syncWithoutDetaching([$adminRole->id]);

        // 2. Manager User (Quản lý kho)
        $manager = User::firstOrCreate(
            ['email' => 'manager@system.com'],
            [
                'name' => 'Warehouse Manager',
                'password' => Hash::make('password'),
                'is_active' => true,
            ]
        );
        $managerRole = Role::where('name', 'manager')->first();
        if ($managerRole) $manager->roles()->syncWithoutDetaching([$managerRole->id]);

        // 3. Customer User
        $customer = User::firstOrCreate(
            ['email' => 'customer@gmail.com'],
            [
                'name' => 'Nguyen Van A',
                'password' => Hash::make('password'),
                'is_active' => true,
            ]
        );
        $custRole = Role::where('name', 'customer')->first();
        if ($custRole) $customer->roles()->syncWithoutDetaching([$custRole->id]);
        
        // Tạo địa chỉ mặc định cho Customer
        $customer->addresses()->create([
            'full_name' => 'Nguyen Van A',
            'phone' => '0987654321',
            'province' => 'Hà Nội',
            'district' => 'Thanh Xuân',
            'ward' => 'Nhân Chính',
            'street' => '123 Lê Văn Lương',
            'is_default' => true
        ]);
    }
}