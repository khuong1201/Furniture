<?php
namespace Modules\Order\database\factories;
use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Order\Domain\Models\Order;
use Modules\User\Domain\Models\User;

class OrderFactory extends Factory {
    protected $model = Order::class;
    public function definition(): array {
        return [
            'uuid' => $this->faker->uuid(),
            'user_id' => User::factory(),
            'shipping_address_snapshot' => ['address' => 'Fake Snapshot Data'], 
            'status' => $this->faker->randomElement(['pending', 'processing', 'shipped', 'delivered', 'cancelled']),
            'payment_status' => $this->faker->randomElement(['unpaid', 'paid']),
            'total_amount' => 0,
            'ordered_at' => $this->faker->dateTimeBetween('-1 year', 'now'),
        ];
    }
}