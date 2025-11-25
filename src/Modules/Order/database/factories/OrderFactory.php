<?php

namespace Modules\Order\database\factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use Modules\Order\Domain\Models\Order;
use Modules\User\Domain\Models\User;

class OrderFactory extends Factory
{
    protected $model = Order::class;

    public function definition(): array
    {
        return [
            'uuid' => Str::uuid(),
            'user_id' => User::factory(),
            'status' => 'pending',
            'payment_status' => 'unpaid',
            'shipping_status' => 'not_shipped',
            'total_amount' => fake()->randomFloat(2, 100, 1000),
        ];
    }
}
