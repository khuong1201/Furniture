<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Role\Database\Seeders\RolePermissionSeeder;
use Modules\User\Domain\Models\User;
use Modules\Role\Domain\Models\Role;
use Modules\Category\Domain\Models\Category;
use Modules\Product\Domain\Models\Product;
use Modules\Warehouse\Domain\Models\Warehouse;
use Modules\Inventory\Domain\Models\Inventory;
use Modules\Order\Domain\Models\Order;
use Modules\Order\Domain\Models\OrderItem;
use Modules\Address\Domain\Models\Address;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Chạy Permissions trước
        $this->call(RolePermissionSeeder::class);

        // 2. Tạo Admin
        $adminUser = User::factory()->admin()->create();
        $adminRole = Role::where('name', 'admin')->first();
        if ($adminRole) $adminUser->roles()->attach($adminRole);

        // 3. Tạo Customers và Addresses
        $customers = User::factory(50)->create()->each(function ($user) {
            Address::factory(rand(1, 2))->create([
                'user_id' => $user->id, 
                'is_default' => true
            ]);
            
            $customerRole = Role::where('name', 'customer')->first();
            if ($customerRole) $user->roles()->attach($customerRole);
        });

        // === [QUAN TRỌNG] === 
        // Load lại danh sách customer kèm relationship addresses để tránh lỗi null
        $customers = User::with('addresses')->whereIn('id', $customers->pluck('id'))->get();

        // 4. Tạo Category & Warehouse
        $categories = Category::factory(10)->create();
        $warehouses = Warehouse::factory(3)->create();

        // 5. Tạo Product & Inventory
        $products = Product::factory(100)->make()->each(function ($product) use ($categories, $warehouses) {
            $product->category_id = $categories->random()->id;
            $product->save();

            // Random sản phẩm này sẽ nằm ở 1 đến 3 kho
            $randomWarehouses = $warehouses->random(rand(1, 3));
            foreach ($randomWarehouses as $wh) {
                Inventory::factory()->create([
                    'product_id' => $product->id,
                    'warehouse_id' => $wh->id,
                    'stock_quantity' => rand(50, 200)
                ]);
            }
        });

        // 6. Tạo Orders
        Order::factory(200)->make()->each(function ($order) use ($customers, $products, $warehouses) {
            $user = $customers->random();
            
            // Fix lỗi null: Nếu user không có address nào thì để null hoặc array rỗng
            $address = $user->addresses->first();

            $order->user_id = $user->id;
            $order->shipping_address_snapshot = $address ? $address->toArray() : [];
            $order->save();

            $totalAmount = 0;
            $orderProducts = $products->random(rand(1, 5)); // Lấy random từ tập products đã tạo

            foreach ($orderProducts as $product) {
                $qty = rand(1, 3);
                $price = $product->price;
                $subtotal = $price * $qty;
                
                // [LOGIC MỚI] Tìm xem kho nào có chứa sản phẩm này
                $inventory = Inventory::where('product_id', $product->id)->inRandomOrder()->first();
                // Nếu tìm thấy kho chứa thì lấy ID kho đó, nếu không thì lấy đại kho đầu tiên (fallback)
                $whId = $inventory ? $inventory->warehouse_id : $warehouses->first()->id;

                OrderItem::create([
                    'uuid' => \Str::uuid(),
                    'order_id' => $order->id,
                    'product_id' => $product->id,
                    'warehouse_id' => $whId,
                    'quantity' => $qty,
                    'original_price' => $price,
                    'discount_amount' => 0,
                    'unit_price' => $price,
                    'subtotal' => $subtotal
                ]);

                $totalAmount += $subtotal;
            }

            $order->update(['total_amount' => $totalAmount]);
        });

        echo "Seeding completed! \n";
        echo "Admin: admin@system.com / password \n";
    }
}