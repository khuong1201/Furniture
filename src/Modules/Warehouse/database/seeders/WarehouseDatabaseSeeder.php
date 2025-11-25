<?php

namespace Modules\Warehouse\database\seeders;

use Illuminate\Database\Seeder;
use Modules\Warehouse\Domain\Models\Warehouse;

class WarehouseDatabaseSeeder extends Seeder
{
    public function run(): void
    {
        Warehouse::factory()->count(3)->create();
    }
}
