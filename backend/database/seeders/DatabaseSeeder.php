<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Modules\Role\database\seeders\RolePermissionSeeder;

// Models
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
use Modules\Order\Domain\Models\Order;
use Modules\Review\Domain\Models\Review;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Dọn dẹp DB
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        $tables = [
            'users', 'roles', 'permissions', 'model_has_roles', 'permission_role', 'role_user',
            'categories', 'products', 'product_variants', 'product_images', 
            'attributes', 'attribute_values', 'variant_attribute_values',
            'warehouses', 'inventory_stocks', 
            'orders', 'order_items', 'carts', 'cart_items', 
            'addresses', 'collections', 'collection_product', 'shippings', 'payments', 'reviews'
        ];

        foreach ($tables as $table) {
            if (Schema::hasTable($table)) DB::table($table)->truncate();
        }
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        echo "🚀 Starting System Seeding...\n";

        // 2. Roles & Permissions
        $this->call(RolePermissionSeeder::class);

        // 3. Admin & User
        echo "👤 Creating Users...\n";
        $admin = User::create([
            'uuid' => Str::uuid(), 'name' => 'Super Admin', 'email' => 'admin@system.com',
            'password' => bcrypt('123456'), 'is_active' => true, 'email_verified_at' => now()
        ]);
        $adminRole = Role::where('name', 'admin')->first();
        if ($adminRole) $admin->roles()->sync([$adminRole->id]); // Sync sẽ tự xử lý bảng trung gian

        $customer = User::create([
            'uuid' => Str::uuid(), 'name' => 'Test Customer', 'email' => 'customer@test.com',
            'password' => bcrypt('123456'), 'is_active' => true, 'email_verified_at' => now()
        ]);
        $customerRole = Role::where('name', 'customer')->first();
        if ($customerRole) $customer->roles()->sync([$customerRole->id]);

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
        $catFashion = Category::create(['uuid' => Str::uuid(), 'name' => 'Thời trang', 'slug' => 'thoi-trang']);
        $catElec = Category::create(['uuid' => Str::uuid(), 'name' => 'Điện tử', 'slug' => 'dien-tu']);

        // 6. Attributes
        echo "🎨 Creating Attributes...\n";
        $attrColor = Attribute::create(['uuid' => Str::uuid(), 'name' => 'Màu sắc', 'slug' => 'color', 'type' => 'color']);
        $valRed = $attrColor->values()->create(['uuid' => Str::uuid(), 'value' => 'Đỏ', 'code' => '#FF0000']);
        $valBlue = $attrColor->values()->create(['uuid' => Str::uuid(), 'value' => 'Xanh', 'code' => '#0000FF']);

        $attrSize = Attribute::create(['uuid' => Str::uuid(), 'name' => 'Kích thước', 'slug' => 'size', 'type' => 'select']);
        $valS = $attrSize->values()->create(['uuid' => Str::uuid(), 'value' => 'S']);
        $valM = $attrSize->values()->create(['uuid' => Str::uuid(), 'value' => 'M']);

        // 7. Products (Loop 20 items)
        echo "📦 Creating Products...\n";
        
        $products = []; // Lưu lại để tạo Order sau

        for ($i = 1; $i <= 20; $i++) {
            $isFashion = $i <= 10;
            $product = Product::create([
                'uuid' => Str::uuid(),
                'name' => $isFashion ? "Áo Thun Mẫu $i" : "Laptop Model $i",
                'category_id' => $isFashion ? $catFashion->id : $catElec->id,
                'has_variants' => $isFashion, 
                'is_active' => true,
                'price' => $isFashion ? null : 20000000,
                'sku' => $isFashion ? null : "LAPTOP-$i",
                // Khởi tạo giá trị thống kê
                'sold_count' => rand(0, 50), 
                'rating_avg' => rand(3, 5), 
                'rating_count' => rand(1, 10)
            ]);
            
            ProductImage::create(['uuid' => Str::uuid(), 'product_id' => $product->id, 'image_url' => 'https://placehold.co/400', 'is_primary' => true]);

            if ($isFashion) {
                // Variant 1
                $v1 = ProductVariant::create(['uuid' => Str::uuid(), 'product_id' => $product->id, 'sku' => "TSHIRT-$i-RED", 'price' => 100000, 'weight' => 0.2]);
                $v1->attributeValues()->sync([$valRed->id, $valS->id]);
                InventoryStock::create(['uuid' => Str::uuid(), 'warehouse_id' => $whHN->id, 'product_variant_id' => $v1->id, 'quantity' => 100]);
                
                // Variant 2
                $v2 = ProductVariant::create(['uuid' => Str::uuid(), 'product_id' => $product->id, 'sku' => "TSHIRT-$i-BLUE", 'price' => 120000, 'weight' => 0.2]);
                $v2->attributeValues()->sync([$valBlue->id, $valM->id]);
                InventoryStock::create(['uuid' => Str::uuid(), 'warehouse_id' => $whHN->id, 'product_variant_id' => $v2->id, 'quantity' => 100]);
            } else {
                // Simple Product
                $vSimple = ProductVariant::create(['uuid' => Str::uuid(), 'product_id' => $product->id, 'sku' => "LAPTOP-$i", 'price' => 20000000, 'weight' => 2.5]);
                InventoryStock::create(['uuid' => Str::uuid(), 'warehouse_id' => $whHCM->id, 'product_variant_id' => $vSimple->id, 'quantity' => 20]);
            }

            $products[] = $product;

            // Tạo Review mẫu
            Review::create([
                'uuid' => Str::uuid(),
                'user_id' => $customer->id,
                'product_id' => $product->id,
                'rating' => 5,
                'comment' => 'Sản phẩm tốt!',
                'is_approved' => true
            ]);
        }

        // 8. Collection
        if (Schema::hasTable('collections')) {
            $col = Collection::create(['uuid' => Str::uuid(), 'name' => 'Flash Sale', 'slug' => 'flash-sale', 'is_active' => true]);
            $col->products()->attach($products[0]->id);
        }

        // 9. Order (Tạo đơn hàng mẫu để test Dashboard)
        echo "🛒 Creating Orders...\n";
        if (Schema::hasTable('orders')) {
            // Lấy 1 variant có sẵn để mua
            $buyVariant = ProductVariant::first();
            
            $order = Order::create([
                'uuid' => Str::uuid(),
                'user_id' => $customer->id,
                'status' => 'delivered',
                'payment_status' => 'paid',
                'total_amount' => $buyVariant->price * 2,
                'ordered_at' => now(),
                'shipping_address_snapshot' => []
            ]);

            $order->items()->create([
                'uuid' => Str::uuid(),
                'order_id' => $order->id,
                'product_variant_id' => $buyVariant->id,
                'warehouse_id' => $whHN->id,
                'quantity' => 2,
                'unit_price' => $buyVariant->price,
                'original_price' => $buyVariant->price,
                'subtotal' => $buyVariant->price * 2
            ]);
        }

        echo "✅ SEEDING COMPLETE! \n";
        echo "Admin: admin@system.com / 123456 \n";
    }
}