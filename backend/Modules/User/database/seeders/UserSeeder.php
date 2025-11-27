<?php

namespace Modules\User\database\seeders;

use Illuminate\Database\Seeder;
use Modules\User\Models\User;

class UserSeeder extends Seeder
{
    public function run()
    {
        User::factory()->count(5)->create();

        User::firstOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name' => 'Admin',
                'password' => bcrypt('password'),
                'is_active' => true,
            ]
        );
        
        $adminRoleId = DB::table('roles')->insertGetId([
            'name' => 'admin',
            'description' => 'Administrator role',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Tạo user admin
        $admin = User::create([
            'uuid' => \Illuminate\Support\Str::uuid(),
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'phone' => '0123456789',
            'password' => Hash::make('password'),
            'is_active' => true,
            'avatar_url' => null,
        ]);

        // Gán role admin cho user
        DB::table('role_user')->insert([
            'role_id' => $adminRoleId,
            'user_id' => $admin->id,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
