<?php

namespace Modules\Shipping\database\factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Shipping\Domain\Models\Shipping;
use Modules\Order\Domain\Models\Order;
use Illuminate\Support\Str;

class ShippingFactory extends Factory
{
    protected $model = Shipping::class;

    public function definition()
    {
        return [
            'uuid' => Str::uuid(),
            'order_id' => Order::factory(),
            'provider' => $this->faker->randomElement(['DHL', 'VNPost', 'GHN']),
            'tracking_number' => strtoupper($this->faker->bothify('TRK###???')),
            'status' => 'pending',
            'shipped_at' => null,
            'delivered_at' => null,
        ];
    }
}
