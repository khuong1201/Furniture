<?php
namespace Modules\Warehouse\database\factories;
use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Warehouse\Domain\Models\Warehouse;

class WarehouseFactory extends Factory {
    protected $model = Warehouse::class;
    public function definition(): array {
        return [
            'uuid' => $this->faker->uuid(),
            'name' => $this->faker->city() . ' Warehouse',
            'location' => $this->faker->address(),
        ];
    }
}