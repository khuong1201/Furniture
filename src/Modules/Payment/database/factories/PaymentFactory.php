<?php

namespace Modules\Payment\database\factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Payment\Domain\Models\Payment;

class PaymentFactory extends Factory
{
    protected $model = Payment::class;

    public function definition(): array
    {
        return [
            'uuid' => $this->faker->uuid,
            'order_id' => 1,
            'method' => $this->faker->randomElement(['credit_card', 'paypal', 'cash']),
            'status' => $this->faker->randomElement(['pending', 'paid', 'failed']),
            'paid_at' => now(),
            'transaction_id' => $this->faker->uuid,
        ];
    }
}
