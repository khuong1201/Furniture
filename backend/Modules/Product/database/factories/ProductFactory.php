<?php
namespace Modules\Product\database\factories;
use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Product\Domain\Models\Product;
use Modules\Category\Domain\Models\Category;

class ProductFactory extends Factory {
    protected $model = Product::class;
    public function definition(): array {
        $name = ucfirst($this->faker->words(3, true));
        return [
            'uuid' => $this->faker->uuid(),
            'name' => $name,
            'description' => $this->faker->paragraph(),
            'price' => $this->faker->numberBetween(100, 10000) * 1000,
            'category_id' => Category::factory(),
            'sku' => strtoupper($this->faker->unique()->bothify('PROD-####-????')),
            'weight' => $this->faker->randomFloat(2, 0.1, 10),
            'status' => true,
        ];
    }
}