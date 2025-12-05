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

        Warehouse::firstOrCreate(
            ['name' => 'Kho Hà Nội'],
            [
                'location' => 'Khu CN Nam Thăng Long, Hà Nội',
                'manager_id' => $manager?->id,
                'is_active' => true
            ]
        );

        Warehouse::firstOrCreate(
            ['name' => 'Kho Hồ Chí Minh'],
            [
                'location' => 'Khu Chế Xuất Tân Thuận, Quận 7, TP.HCM',
                'manager_id' => $manager?->id,
                'is_active' => true
            ]
        );
    }
}