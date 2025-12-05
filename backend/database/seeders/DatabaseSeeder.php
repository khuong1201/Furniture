<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

// Import Modules Seeders
use Modules\Permission\Database\Seeders\PermissionDatabaseSeeder;
use Modules\User\Database\Seeders\UserDatabaseSeeder;
use Modules\Category\Database\Seeders\CategoryDatabaseSeeder; // Phòng/Loại
use Modules\Product\Database\Seeders\ProductDatabaseSeeder;   // Sofa, Giường...
use Modules\Warehouse\Database\Seeders\WarehouseDatabaseSeeder;
use Modules\Inventory\Database\Seeders\InventoryDatabaseSeeder; // Nhập kho
use Modules\Voucher\Database\Seeders\VoucherDatabaseSeeder;
// use Modules\Order\Database\Seeders\OrderDatabaseSeeder; // Optional, chạy sau cùng nếu cần đơn mẫu
use Modules\Review\Database\Seeders\ReviewDatabaseSeeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            PermissionDatabaseSeeder::class,
            UserDatabaseSeeder::class,
            WarehouseDatabaseSeeder::class, // Tạo kho trước
            CategoryDatabaseSeeder::class,  // Tạo danh mục nội thất
            ProductDatabaseSeeder::class,   // Tạo sp nội thất + variants
            InventoryDatabaseSeeder::class, // Nhập kho cho variants vừa tạo
            VoucherDatabaseSeeder::class,
            ReviewDatabaseSeeder::class,
        ]);
    }
}