<?php

namespace Modules\Order\database\seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use Modules\Order\Models\Order;
use Modules\Order\Models\OrderItem;
use Modules\User\Models\User;
use Modules\Product\Models\Product;
use Modules\Address\Models\Address;

class OrderDatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = User::all();
        $products = Product::all();
        $addresses = Address::all();

        if ($users->isEmpty() || $products->isEmpty() || $addresses->isEmpty()) {
            $this->command->warn('⚠️ Cần có dữ liệu trong bảng users, products và addresses trước khi seed orders.');
            return;
        }

        // Tạo 10 đơn hàng mẫu
        foreach (range(1, 10) as $i) {
            $user = $users->random();
            $address = $addresses->where('user_id', $user->id)->first() ?? $addresses->random();

            $order = Order::create([
                'uuid' => Str::uuid(),
                'user_id' => $user->id,
                'address_id' => $address->id,
                'status' => fake()->randomElement(['pending', 'processing', 'shipped', 'delivered', 'cancelled']),
                'total_amount' => 0, // sẽ cập nhật sau
                'payment_status' => fake()->randomElement(['unpaid', 'paid', 'refunded']),
                'shipping_status' => fake()->randomElement(['not_shipped', 'shipped', 'delivered']),
            ]);

            $total = 0;
            // Mỗi đơn hàng có từ 1-4 sản phẩm
            foreach (range(1, rand(1, 4)) as $j) {
                $product = $products->random();
                $quantity = rand(1, 5);
                $unitPrice = $product->price ?? fake()->randomFloat(2, 10, 500);

                OrderItem::create([
                    'uuid' => Str::uuid(),
                    'order_id' => $order->id,
                    'product_id' => $product->id,
                    'quantity' => $quantity,
                    'unit_price' => $unitPrice,
                ]);

                $total += $quantity * $unitPrice;
            }

            // Cập nhật tổng tiền cho đơn hàng
            $order->update(['total_amount' => $total]);
        }

        $this->command->info('✅ Đã seed dữ liệu mẫu cho orders và order_items thành công!');
    }
}
