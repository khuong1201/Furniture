<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Modules\Role\database\seeders\RolePermissionSeeder;
use Modules\Role\database\seeders\AdminSeeder;

use Modules\User\Domain\Models\User;
use Modules\Role\Domain\Models\Role;
use Modules\Category\Domain\Models\Category;
use Modules\Product\Domain\Models\Product;
use Modules\Product\Domain\Models\ProductVariant;
use Modules\Product\Domain\Models\Attribute;
use Modules\Product\Domain\Models\AttributeValue;
use Modules\Product\Domain\Models\ProductImage;
use Modules\Warehouse\Domain\Models\Warehouse;
use Modules\Inventory\Domain\Models\InventoryStock;
use Modules\Address\Domain\Models\Address;
use Modules\Collection\Domain\Models\Collection;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Dọn dẹp DB
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        $tables = [
            'users',
            'roles',
            'permissions',
            'model_has_roles',
            'permission_role',
            'categories',
            'products',
            'product_variants',
            'product_images',
            'attributes',
            'attribute_values',
            'variant_attribute_values',
            'warehouses',
            'inventory_stocks',
            'orders',
            'order_items',
            'carts',
            'cart_items',
            'addresses',
            'collections',
            'collection_product',
            'shippings',
            'payments'
        ];

        foreach ($tables as $table) {
            if (Schema::hasTable($table)) {
                DB::table($table)->truncate();
            }
        }

        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        echo "🚀 Starting System Seeding...\n";

        $this->call(RolePermissionSeeder::class);
        $this->call(AdminSeeder::class);

        // Admin created in AdminSeeder, so we don't need to create it here again.
        // Retrieve admin for warehouse assignment
        $admin = User::where('email', 'admin@system.com')->first();

        $customer = User::create([
            'uuid' => Str::uuid(),
            'name' => 'Test Customer',
            'email' => 'customer@test.com',
            'password' => bcrypt('123456'),
            'is_active' => true
        ]);
        $customerRole = Role::where('name', 'customer')->first();
        if ($customerRole)
            $customer->roles()->sync([$customerRole->id]);

        // Address
        if (Schema::hasTable('addresses')) {
            Address::create(['uuid' => Str::uuid(), 'user_id' => $customer->id, 'full_name' => 'Khách Hàng A', 'phone' => '0909123456', 'province' => 'Hà Nội', 'district' => 'Cầu Giấy', 'ward' => 'Dịch Vọng', 'street' => '123 Xuân Thủy', 'is_default' => true]);
        }

        // 4. Warehouse
        echo "🏭 Creating Warehouses...\n";
        $whHN = Warehouse::create(['uuid' => Str::uuid(), 'name' => 'Kho Hà Nội', 'location' => 'Hà Nội', 'manager_id' => $admin->id]);
        $whHCM = Warehouse::create(['uuid' => Str::uuid(), 'name' => 'Kho HCM', 'location' => 'Hồ Chí Minh', 'manager_id' => $admin->id]);

        // 5. Categories
        echo "📂 Creating Categories...\n";
        $categories = [];
        $categoryNames = ['Thời trang', 'Điện tử', 'Nội thất', 'Gia dụng', 'Sách', 'Thể thao'];
        foreach ($categoryNames as $name) {
            $categories[] = Category::create([
                'uuid' => Str::uuid(),
                'name' => $name,
                'slug' => Str::slug($name)
            ]);
        }

        // 6. Attributes (Mới)
        echo "🎨 Creating Attributes...\n";
        $attrColor = Attribute::create(['uuid' => Str::uuid(), 'name' => 'Màu sắc', 'slug' => 'color', 'type' => 'color']);
        $colors = [
            ['value' => 'Đỏ', 'code' => '#FF0000'],
            ['value' => 'Xanh', 'code' => '#0000FF'],
            ['value' => 'Vàng', 'code' => '#FFFF00'],
            ['value' => 'Đen', 'code' => '#000000'],
            ['value' => 'Trắng', 'code' => '#FFFFFF']
        ];
        $colorValues = [];
        foreach ($colors as $c) {
            $colorValues[] = $attrColor->values()->create(['uuid' => Str::uuid(), 'value' => $c['value'], 'code' => $c['code']]);
        }

        $attrSize = Attribute::create(['uuid' => Str::uuid(), 'name' => 'Kích thước', 'slug' => 'size', 'type' => 'select']);
        $sizes = ['S', 'M', 'L', 'XL', 'XXL'];
        $sizeValues = [];
        foreach ($sizes as $s) {
            $sizeValues[] = $attrSize->values()->create(['uuid' => Str::uuid(), 'value' => $s]);
        }

        echo "📦 Creating Products & Inventory...\n";

        $createdProducts = [];

        // Create 50 random products
        for ($i = 1; $i <= 50; $i++) {
            $cat = $categories[array_rand($categories)];
            $hasVariants = rand(0, 1) == 1;

            $product = Product::create([
                'uuid' => Str::uuid(),
                'name' => $cat->name . ' Sản phẩm ' . $i,
                'category_id' => $cat->id,
                'has_variants' => $hasVariants,
                'price' => $hasVariants ? 0 : rand(100, 5000) * 1000,
                'sku' => 'SP-' . $i,
                'is_active' => true,
                'description' => 'Mô tả chi tiết cho sản phẩm ' . $i
            ]);

            $createdProducts[] = $product;

            if ($hasVariants) {
                // Create 3 variants for this product
                for ($j = 1; $j <= 3; $j++) {
                    $color = $colorValues[array_rand($colorValues)];
                    $size = $sizeValues[array_rand($sizeValues)];

                    $variant = ProductVariant::create([
                        'uuid' => Str::uuid(),
                        'product_id' => $product->id,
                        'sku' => 'SP-' . $i . '-V' . $j,
                        'price' => rand(100, 5000) * 1000,
                        'weight' => rand(1, 10) / 10
                    ]);

                    $variant->attributeValues()->sync([$color->id, $size->id]);

                    // Stock for HN
                    InventoryStock::create([
                        'uuid' => Str::uuid(),
                        'warehouse_id' => $whHN->id,
                        'product_variant_id' => $variant->id,
                        'quantity' => rand(0, 100),
                        'min_threshold' => 5
                    ]);
                }
            } else {
                // Simple product variant (internal)
                $variant = ProductVariant::create([
                    'uuid' => Str::uuid(),
                    'product_id' => $product->id,
                    'sku' => 'SP-' . $i,
                    'price' => $product->price,
                    'weight' => rand(1, 10) / 10
                ]);

                InventoryStock::create([
                    'uuid' => Str::uuid(),
                    'warehouse_id' => $whHN->id,
                    'product_variant_id' => $variant->id,
                    'quantity' => rand(0, 100),
                    'min_threshold' => 5
                ]);
            }
        }

        if (Schema::hasTable('collections') && count($createdProducts) > 0) {
            $colFlashSale = Collection::create(['uuid' => Str::uuid(), 'name' => 'Flash Sale', 'slug' => 'flash-sale', 'is_active' => true]);
            // Attach random 5 products
            $randomProducts = collect($createdProducts)->random(min(5, count($createdProducts)));
            $colFlashSale->products()->attach($randomProducts->pluck('id'));
        }

        echo "📦 Creating Orders...\n";
        // Create 20 random orders
        $statuses = ['pending', 'processing', 'shipped', 'delivered', 'cancelled'];

        for ($i = 1; $i <= 20; $i++) {
            $status = $statuses[array_rand($statuses)];
            $orderUser = $customer; // Default to test customer

            $order = \Modules\Order\Domain\Models\Order::create([
                'uuid' => Str::uuid(),
                'user_id' => $orderUser->id,
                'shipping_address_snapshot' => [
                    'full_name' => $orderUser->name,
                    'phone' => '0909123456',
                    'address' => '123 Test Street, Hanoi'
                ],
                'status' => $status,
                'payment_status' => ($status == 'delivered' || $status == 'shipped') ? 'paid' : 'unpaid',
                'shipping_status' => $status == 'delivered' ? 'delivered' : ($status == 'shipped' ? 'shipped' : 'not_shipped'),
                'total_amount' => 0, // Will update after adding items
                'ordered_at' => now()->subDays(rand(0, 30)),
                'notes' => 'Giao hàng giờ hành chính'
            ]);

            // Add 1-5 items per order
            $itemCount = rand(1, 5);
            $totalAmount = 0;

            for ($j = 0; $j < $itemCount; $j++) {
                // Pick random product
                $prod = $createdProducts[array_rand($createdProducts)];
                // Pick random variant of that product (or create fake one if needed, but we should use existing)
                // Since we didn't store variants in a flat list, let's query or just use the first variant of the product
                $variant = \Modules\Product\Domain\Models\ProductVariant::where('product_id', $prod->id)->first();

                if ($variant) {
                    $qty = rand(1, 3);
                    $price = $variant->price;
                    $subtotal = $price * $qty;

                    \Modules\Order\Domain\Models\OrderItem::create([
                        'uuid' => Str::uuid(),
                        'order_id' => $order->id,
                        'product_variant_id' => $variant->id,
                        'warehouse_id' => $whHN->id,
                        'quantity' => $qty,
                        'unit_price' => $price,
                        'subtotal' => $subtotal,
                        'original_price' => $price,
                        'product_snapshot' => [
                            'name' => $prod->name,
                            'sku' => $variant->sku,
                            'variant_options' => 'Màu/Size' // Simplified
                        ]
                    ]);

                    $totalAmount += $subtotal;
                }
            }

            $order->update(['total_amount' => $totalAmount]);
        }

        echo "✅ SEEDING COMPLETE! \n";
        echo "Admin: admin@system.com / 123456 \n";
        echo "Customer: customer@test.com / 123456 \n";
    }
}