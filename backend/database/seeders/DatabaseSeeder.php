<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema; // <--- ĐÃ THÊM DÒNG NAY (Quan trọng)
use Illuminate\Support\Str;
use Modules\Role\database\seeders\RolePermissionSeeder;

// Import Models từ Modules
use Modules\User\Domain\Models\User;
use Modules\Role\Domain\Models\Role;
use Modules\Category\Domain\Models\Category;
use Modules\Product\Domain\Models\Product;
use Modules\Product\Domain\Models\ProductImage;
use Modules\Warehouse\Domain\Models\Warehouse;
use Modules\Inventory\Domain\Models\Inventory;
use Modules\Address\Domain\Models\Address;
use Modules\Collection\Domain\Models\Collection;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Dọn dẹp dữ liệu cũ (Reset Database)
        // Tắt check khóa ngoại để truncate được các bảng có quan hệ
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        
        $tables = [
            'users', 'roles', 'permissions', 'model_has_roles', 'permission_role',
            'categories', 'products', 'product_images', 
            'warehouses', 'inventories', 
            'orders', 'order_items', 'carts', 'cart_items', 
            'addresses', 'collections', 'collection_product'
        ];

        foreach ($tables as $table) {
            // Kiểm tra bảng tồn tại trước khi truncate
            if (Schema::hasTable($table)) { // <--- Đã sửa: Dùng Schema Facade đã import
                DB::table($table)->truncate();
            }
        }
        
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        echo "🚀 Starting System Seeding...\n";

        // 2. Roles & Permissions (Quan trọng nhất - Phải chạy trước)
        $this->call(RolePermissionSeeder::class);

        // 3. Tạo Admin System
        echo "👤 Creating Admin & Users...\n";
        $admin = User::create([
            'uuid' => Str::uuid(),
            'name' => 'Super Admin',
            'email' => 'admin@system.com',
            'password' => bcrypt('123456'),
            'email_verified_at' => now(),
            'is_active' => true
        ]);
        
        // Gán Role Admin
        $adminRole = Role::where('name', 'admin')->first();
        if ($adminRole) {
            // Lưu ý: Nếu dùng package spatie/laravel-permission thì dùng $admin->assignRole('admin');
            // Nếu dùng quan hệ M-M tự viết:
            $admin->roles()->sync([$adminRole->id]);
        }

        // 4. Tạo Khách hàng mẫu
        $customer = User::create([
            'uuid' => Str::uuid(),
            'name' => 'Test Customer',
            'email' => 'customer@test.com',
            'password' => bcrypt('123456'),
            'email_verified_at' => now(),
            'is_active' => true
        ]);
        
        $customerRole = Role::where('name', 'customer')->first();
        if ($customerRole) {
            $customer->roles()->sync([$customerRole->id]);
        }

        // 5. Tạo địa chỉ cho khách
        if (Schema::hasTable('addresses')) {
            Address::create([
                'uuid' => Str::uuid(),
                'user_id' => $customer->id,
                'full_name' => 'Khách Hàng A',
                'phone' => '0909123456',
                'province' => 'Hà Nội',
                'district' => 'Cầu Giấy',
                'ward' => 'Dịch Vọng',
                'street' => '123 Xuân Thủy',
                'is_default' => true
            ]);
        }

        // 6. Tạo Kho hàng
        echo "🏭 Creating Warehouses...\n";
        if (Schema::hasTable('warehouses')) {
            $whHN = Warehouse::create([
                'uuid' => Str::uuid(), 
                'name' => 'Kho Trung Tâm Hà Nội', 
                'location' => 'Hà Nội',
                'manager_id' => $admin->id
            ]);
            
            $whHCM = Warehouse::create([
                'uuid' => Str::uuid(), 
                'name' => 'Kho Hồ Chí Minh', 
                'location' => 'Hồ Chí Minh',
                'manager_id' => $admin->id
            ]);
        }

        // 7. Tạo Danh mục
        echo "📂 Creating Categories...\n";
        $catPhone = Category::create([
            'uuid' => Str::uuid(), 
            'name' => 'Điện thoại', 
            'slug' => 'dien-thoai',
            'description' => 'Smartphone chính hãng'
        ]);
        
        $catLaptop = Category::create([
            'uuid' => Str::uuid(), 
            'name' => 'Laptop', 
            'slug' => 'laptop',
            'description' => 'Laptop văn phòng & Gaming'
        ]);

        // 8. Tạo Collection
        echo "🔥 Creating Collections...\n";
        if (Schema::hasTable('collections')) {
            $colFlashSale = Collection::create([
                'uuid' => Str::uuid(),
                'name' => 'Flash Sale Tháng 12',
                'slug' => 'flash-sale-dec',
                'is_active' => true
            ]);
        }

        // 9. Tạo Sản phẩm & Nhập kho
        echo "📦 Creating Products & Inventory...\n";
        
        for ($i = 1; $i <= 10; $i++) {
            $isPhone = $i <= 5;
            $product = Product::create([
                'uuid' => Str::uuid(),
                'name' => $isPhone ? "iPhone 15 Pro Max V$i" : "Macbook Pro M3 V$i",
                'description' => "Mô tả chi tiết cho sản phẩm $i...",
                'price' => rand(1000, 3000) * 1000, 
                'category_id' => $isPhone ? $catPhone->id : $catLaptop->id,
                'sku' => "SP-00$i",
                'status' => true 
            ]);

            // Tạo ảnh
            ProductImage::create([
                'uuid' => Str::uuid(), 
                'product_id' => $product->id, 
                'image_url' => 'https://via.placeholder.com/400x400.png?text=Product+' . $i, 
                'is_primary' => true
            ]);

            // Nhập kho HN (Nếu bảng tồn tại)
            if (isset($whHN) && Schema::hasTable('inventories')) {
                Inventory::create([
                    'uuid' => Str::uuid(),
                    'product_id' => $product->id,
                    'warehouse_id' => $whHN->id,
                    'stock_quantity' => 50, 
                    'min_threshold' => 5,  
                    'status' => 'in_stock'
                ]);
            }

            // Gán vào Collection (Nếu có)
            if ($i <= 3 && isset($colFlashSale)) {
                $colFlashSale->products()->attach($product->id);
            }
        }

        echo "✅ SEEDING COMPLETE! \n";
        echo "------------------------------------------------\n";
        echo "Admin:    admin@system.com / 123456 \n";
        echo "Customer: customer@test.com / 123456 \n";
        echo "------------------------------------------------\n";
    }
}