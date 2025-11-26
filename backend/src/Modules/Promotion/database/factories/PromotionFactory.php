<?php

namespace Modules\Promotion\database\factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Promotion\Domain\Models\Promotion;
use Illuminate\Support\Str;

class PromotionFactory extends Factory
{
    protected $model = Promotion::class;

    public function definition()
    {
        return [
            'uuid' => Str::uuid(),
            'name' => $this->faker->sentence(3),
            'type' => $this->faker->randomElement(['percentage', 'fixed']),
            'value' => $this->faker->randomFloat(2, 5, 50),
            'start_date' => now(),
            'end_date' => now()->addDays(10),
            'status' => true,
        ];
    }
}
