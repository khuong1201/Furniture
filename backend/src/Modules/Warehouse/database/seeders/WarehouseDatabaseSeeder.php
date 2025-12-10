<?php

namespace Modules\Warehouse\database\seeders;

use Illuminate\Database\Seeder;
use Modules\Warehouse\Domain\Models\Warehouse;
use Modules\User\Domain\Models\User;

class WarehouseDatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $manager = User::where('email', 'manager@system.com')->first();
        $managerId = $manager?->id;

        Warehouse::firstOrCreate(
            ['name' => 'North Distribution Center'],
            [
                'location' => 'Industrial Zone A, Hanoi',
                'manager_id' => $managerId,
                'is_active' => true
            ]
        );

        Warehouse::firstOrCreate(
            ['name' => 'South Distribution Center'],
            [
                'location' => 'District 7, Ho Chi Minh City',
                'manager_id' => $managerId,
                'is_active' => true
            ]
        );

        Warehouse::firstOrCreate(
            ['name' => 'Central Showroom'],
            [
                'location' => 'Da Nang City Center',
                'manager_id' => $managerId,
                'is_active' => true
            ]
        );
    }
}