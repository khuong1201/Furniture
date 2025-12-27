<?php

namespace Modules\Inventory\database\factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use Modules\Inventory\Domain\Models\InventoryStock;

class InventoryStockFactory extends Factory
{
    protected $model = InventoryStock::class;

    public function definition(): array
    {
        return [
            'uuid' => (string) Str::uuid(),
            'quantity' => $this->faker->numberBetween(10, 100), // Mặc định random
            'min_threshold' => 10,
            // warehouse_id và product_variant_id sẽ được truyền vào khi gọi
        ];
    }

    // State đặc biệt để khởi tạo kho = 0
    public function zero()
    {
        return $this->state(fn (array $attributes) => [
            'quantity' => 0,
        ]);
    }
}