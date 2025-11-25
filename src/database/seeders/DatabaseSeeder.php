<?php

namespace database\seeders;

use Illuminate\Database\Seeder;
use Modules\Category\database\seeders\CategoryDatabaseSeeder;
use Modules\Product\database\seeders\ProductDatabaseSeeder;
use Modules\Order\database\seeders\OrderDatabaseSeeder;
use Modules\Inventory\database\seeders\InventoryDatabaseSeeder;
use Modules\Warehouse\database\seeders\WarehouseDatabaseSeeder;
class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            CategoryDatabaseSeeder::class,
            InventoryDatabaseSeeder::class,
            WarehouseDatabaseSeeder::class,
            ProductDatabaseSeeder::class,
            // OrderDatabaseSeeder::class,
        ]);
    }
}
