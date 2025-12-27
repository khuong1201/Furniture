<?php

namespace Modules\Order\database\factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use Modules\Order\Domain\Models\Order;
use Modules\Order\Enums\OrderStatus;
use Modules\Order\Enums\PaymentStatus;

class OrderFactory extends Factory
{
    protected $model = Order::class;

    public function definition(): array
    {
        // Lưu ý: Các field như user_id, shipping_address_snapshot, ordered_at 
        // sẽ được truyền đè từ Seeder vào để đảm bảo logic.
        return [
            'uuid' => (string) Str::uuid(),
            'code' => 'ORD-' . now()->format('Ymd') . '-' . strtoupper(Str::random(8)),
            'status' => OrderStatus::PENDING,
            'payment_status' => PaymentStatus::UNPAID,
            'subtotal' => 0,
            'shipping_fee' => 30000,
            'grand_total' => 0,
            'notes' => $this->faker->optional(0.3)->sentence(), // 30% có note
        ];
    }
}