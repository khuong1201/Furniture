<?php

namespace Modules\Address\database\seeders;

use Illuminate\Database\Seeder;
use Modules\Address\Domain\Models\Address;
use Modules\User\Domain\Models\User;

class AddressDatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::first();
        if ($user) {
            Address::factory()->count(3)->create(['user_id' => $user->id]);
        }
    }
}
