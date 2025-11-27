<?php

namespace Modules\Inventory\database\seeders;

use Illuminate\Database\Seeder;
use Modules\Inventory\Domain\Models\Inventory;
use Modules\Product\Domain\Models\Product;
use Modules\Warehouse\Domain\Models\Warehouse;

class InventoryDatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $warehouses = Warehouse::all();
        $products = Product::all();

        if ($warehouses->isEmpty() || $products->isEmpty()) return;

        foreach ($products as $product) {
            foreach ($warehouses as $warehouse) {
                Inventory::factory()->create([
                    'product_id' => $product->id,
                    'warehouse_id' => $warehouse->id,
                ]);
            }
        }
    }
}
