<?php
namespace Modules\Inventory\database\factories;
use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Inventory\Domain\Models\Inventory;
use Modules\Product\Domain\Models\Product;
use Modules\Warehouse\Domain\Models\Warehouse;

class InventoryFactory extends Factory {
    protected $model = Inventory::class;
    public function definition(): array {
        return [
            'uuid' => $this->faker->uuid(),
            'product_id' => Product::factory(),
            'warehouse_id' => Warehouse::factory(),
            'stock_quantity' => $this->faker->numberBetween(10, 1000),
            'min_threshold' => 10,
            'status' => 'in_stock',
        ];
    }
}