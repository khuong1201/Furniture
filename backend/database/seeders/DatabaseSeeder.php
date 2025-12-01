<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Modules\Role\Database\Seeders\RolePermissionSeeder;

use Modules\User\Domain\Models\User;
use Modules\Role\Domain\Models\Role;
use Modules\Category\Domain\Models\Category;
use Modules\Product\Domain\Models\Product;
use Modules\Product\Domain\Models\ProductImage;
use Modules\Warehouse\Domain\Models\Warehouse;
use Modules\Inventory\Domain\Models\Inventory;
use Modules\Address\Domain\Models\Address;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Xóa sạch dữ liệu cũ
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        $tables = [
            'users', 'roles', 'permissions', 'categories', 'products', 
            'product_images', 'warehouses', 'inventories', 'orders', 
            'order_items', 'carts', 'cart_items', 'addresses'
        ];
        foreach ($tables as $table) {
            DB::table($table)->truncate();
        }
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        echo "🚀 Starting Minimal Seeding (Domain Structure)...\n";

        // 1. Roles & Permissions
        $this->call(RolePermissionSeeder::class);

        // 2. Tạo Admin
        $admin = User::create([
            'uuid' => Str::uuid(),
            'name' => 'Super Admin',
            'email' => 'admin@system.com',
            'password' => bcrypt('123456'),
            'email_verified_at' => now(),
            'is_active' => true
        ]);
        $adminRole = Role::where('name', 'admin')->first();
        if ($adminRole) $admin->roles()->attach($adminRole);

        // 3. Tạo 1 Khách hàng mẫu
        $customer = User::create([
            'uuid' => Str::uuid(),
            'name' => 'Test Customer',
            'email' => 'customer@test.com',
            'password' => bcrypt('123456'),
            'email_verified_at' => now(),
            'is_active' => true
        ]);
        $customerRole = Role::where('name', 'customer')->first();
        if ($customerRole) $customer->roles()->attach($customerRole);

        // Tạo địa chỉ cho khách
        Address::create([
            'uuid' => Str::uuid(),
            'user_id' => $customer->id,
            'full_name' => 'Test Customer',
            'phone' => '0909123456',
            'province' => 'Hà Nội',
            'district' => 'Cầu Giấy',
            'ward' => 'Dịch Vọng',
            'street' => '123 Xuân Thủy',
            'is_default' => true
        ]);

        // 4. Tạo Kho
        $whHN = Warehouse::create([
            'uuid' => Str::uuid(), 
            'name' => 'Kho Hà Nội', 
            'location' => 'Hà Nội', 
            'manager_id' => $admin->id
        ]);
        
        $whHCM = Warehouse::create([
            'uuid' => Str::uuid(), 
            'name' => 'Kho HCM', 
            'location' => 'Hồ Chí Minh', 
            'manager_id' => $admin->id
        ]);

        // 5. Tạo Danh mục
        $catElectronics = Category::create([
            'uuid' => Str::uuid(), 
            'name' => 'Điện tử', 
            'description' => 'Đồ điện tử, công nghệ',
            'parent_id' => null
        ]);
        
        // 6. Tạo 10 Sản phẩm & Nhập kho
        echo "📦 Creating 10 Products...\n";
        
        for ($i = 1; $i <= 10; $i++) {
            $product = Product::create([
                'uuid' => Str::uuid(),
                'name' => "Sản phẩm Test $i",
                'description' => "Mô tả cho sản phẩm $i",
                'price' => rand(100, 1000) * 1000,
                'category_id' => $catElectronics->id,
                'sku' => "SP00$i",
                'weight' => 0.5,
                'status' => true
            ]);

            // Tạo ảnh
            ProductImage::create([
                'uuid' => Str::uuid(), 
                'product_id' => $product->id, 
                'image_url' => 'https://placehold.co/400', 
                'is_primary' => true
            ]);

            // Nhập kho HN (Mỗi món 100 cái)
            Inventory::create([
                'uuid' => Str::uuid(),
                'product_id' => $product->id,
                'warehouse_id' => $whHN->id,
                'stock_quantity' => 100,
                'min_threshold' => 10,
                'status' => 'in_stock'
            ]);
        }

        echo "✅ DONE! \n";
        echo "Admin: admin@system.com / 123456 \n";
        echo "Customer: customer@test.com / 123456 \n";
    }
}