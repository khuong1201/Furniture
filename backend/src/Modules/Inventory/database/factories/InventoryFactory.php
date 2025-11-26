<?php

namespace Modules\Inventory\database\factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use Modules\Inventory\Domain\Models\Inventory;
use Modules\Product\Domain\Models\Product;
use Modules\Warehouse\Domain\Models\Warehouse;

class InventoryFactory extends Factory
{
    protected $model = Inventory::class;

    public function definition(): array
    {
        $product = Product::inRandomOrder()->first() ?: Product::factory()->create();
        $warehouse = Warehouse::inRandomOrder()->first() ?: Warehouse::factory()->create();

        $qty = $this->faker->numberBetween(0, 200);
        return [
            'uuid' => (string) Str::uuid(),
            'product_id' => $product->id,
            'warehouse_id' => $warehouse->id,
            'stock_quantity' => $qty,
            'min_threshold' => $this->faker->numberBetween(0, 10),
            'max_threshold' => null,
            'status' => $qty <= 0 ? 'out_of_stock' : ($qty <= 5 ? 'low_stock' : 'in_stock'),
        ];
    }
}
