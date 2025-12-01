<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
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
        $this->call(RolePermissionSeeder::class);

        $adminUser = User::factory()->admin()->create();
        $adminRole = Role::where('name', 'admin')->first();
        if ($adminRole) $adminUser->roles()->attach($adminRole);

        $customers = User::factory(50)->create()->each(function ($user) {
            Address::factory(rand(1, 2))->create(['user_id' => $user->id, 'is_default' => true]);
            $customerRole = Role::where('name', 'customer')->first();
            if ($customerRole) $user->roles()->attach($customerRole);
        });

        $categories = Category::factory(10)->create();

        $warehouses = Warehouse::factory(3)->create();

        $products = Product::factory(100)->make()->each(function ($product) use ($categories, $warehouses) {
            $product->category_id = $categories->random()->id;
            $product->save();

            $randomWarehouses = $warehouses->random(rand(1, 3));
            foreach ($randomWarehouses as $wh) {
                Inventory::factory()->create([
                    'product_id' => $product->id,
                    'warehouse_id' => $wh->id,
                    'stock_quantity' => rand(50, 200)
                ]);
            }
        });

        Order::factory(200)->make()->each(function ($order) use ($customers, $products, $warehouses) {
            $user = $customers->random();
            $order->user_id = $user->id;
            $order->shipping_address_snapshot = $user->addresses->first()?->toArray();
            $order->save();

            $totalAmount = 0;
            $orderProducts = Product::inRandomOrder()->limit(rand(1, 5))->get();

            foreach ($orderProducts as $product) {
                $qty = rand(1, 3);
                $price = $product->price;
                $subtotal = $price * $qty;
                
                $whId = $warehouses->first()->id; 

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