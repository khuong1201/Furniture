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
    }
}
