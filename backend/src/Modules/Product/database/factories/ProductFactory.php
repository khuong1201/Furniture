<?php

namespace Modules\Product\database\factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use Modules\Product\Domain\Models\Product;
use Modules\Warehouse\Domain\Models\Warehouse;
class ProductFactory extends Factory
{
    protected $model = Product::class;

    public function definition(): array
    {
        return [
            'uuid' => (string) Str::uuid(),
            'name' => $this->faker->words(3, true),
            'description' => $this->faker->sentence(),
            'price' => $this->faker->randomFloat(2, 10, 1000),
            'sku' => strtoupper($this->faker->bothify('SKU-####-???')),
            'status' => true,
        ];
    }
    public function configure()
    {
        return $this->afterCreating(function (Product $product) {
            $warehouses = Warehouse::inRandomOrder()->take(rand(1, 3))->get();

            foreach ($warehouses as $warehouse) {
                $product->warehouses()->attach($warehouse->id, [
                    'quantity' => rand(1, 50),
                ]);
            }
        });
    }
}
