<?php

namespace Modules\Promotion\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Promotion\Domain\Models\Promotion;

class PromotionFactory extends Factory
{
    protected $model = Promotion::class;

    public function definition(): array
    {
        $type = $this->faker->randomElement(['percentage', 'fixed']);
        
        // Nếu percentage: 1-50%. Nếu fixed: 10,000 - 500,000
        $value = $type === 'percentage' 
            ? $this->faker->numberBetween(5, 50) 
            : $this->faker->numberBetween(10, 500) * 1000;

        return [
            'name' => $this->faker->words(3, true) . ' Sale',
            'description' => $this->faker->sentence,
            'type' => $type,
            'value' => $value,
            'min_order_value' => $this->faker->numberBetween(1, 10) * 100000,
            'max_discount_amount' => $type === 'percentage' ? 200000 : null,
            'quantity' => $this->faker->numberBetween(0, 100),
            'used_count' => 0,
            'limit_per_user' => 1,
            'start_date' => now()->subDays(rand(0, 10)),
            'end_date' => now()->addDays(rand(5, 30)),
            'is_active' => true,
        ];
    }
}