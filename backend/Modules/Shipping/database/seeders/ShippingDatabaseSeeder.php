<?php

namespace Modules\Shipping\database\seeders;

use Illuminate\Database\Seeder;
use Modules\Shipping\Domain\Models\Shipping;
use Modules\Order\Domain\Models\Order;
use Modules\User\Domain\Models\User;

class ShippingDatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Tạo 5 user mẫu
        $users = User::factory()->count(5)->create();

        foreach ($users as $user) {
            // Mỗi user tạo 2 đơn hàng mẫu
            $orders = Order::factory()->count(2)->create([
                'user_id' => $user->id,
                'status' => 'pending',
                'total_amount' => rand(100, 1000),
            ]);

            foreach ($orders as $order) {
                // Tạo 1-2 shipping cho mỗi order
                Shipping::factory()->count(rand(1,2))->create([
                    'order_id' => $order->id,
                    'provider' => 'VNPost',
                    'tracking_number' => 'TRK'.rand(100000,999999),
                    'status' => 'pending',
                ]);
            }
        }
    }
}
