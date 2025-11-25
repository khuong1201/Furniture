<?php

namespace Modules\Review\database\factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use Modules\Review\Domain\Models\Review;
use Modules\User\Domain\Models\User;
use Modules\Product\Domain\Models\Product;

class ReviewFactory extends Factory
{
    protected $model = Review::class;

    public function definition(): array
    {
        return [
            'uuid' => (string) Str::uuid(),
            'user_id' => User::query()->inRandomOrder()->value('id') ?? User::factory(),
            'product_id' => Product::query()->inRandomOrder()->value('id') ?? Product::factory(),
            'rating' => $this->faker->numberBetween(1, 5),
            'comment' => $this->faker->optional()->sentence(),
            'is_approved' => $this->faker->boolean(70),
            'is_deleted' => false,
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}
