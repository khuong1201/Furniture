<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            // --- 1. SYSTEM & CONFIG (Nền tảng) ---
            \Modules\Permission\database\seeders\PermissionDatabaseSeeder::class,
            \Modules\Role\database\seeders\RoleDatabaseSeeder::class,
            \Modules\Currency\database\seeders\CurrencyDatabaseSeeder::class,

            // --- 2. USERS & LOCATIONS (Con người & Địa điểm) ---
            // User phải có trước Address
            \Modules\User\database\seeders\UserDatabaseSeeder::class,
            \Modules\Address\database\seeders\AddressDatabaseSeeder::class,
            
            // Warehouse cần có trước Product để setup tồn kho
            \Modules\Warehouse\database\seeders\WarehouseDatabaseSeeder::class,

            // --- 3. CATALOG (Hàng hóa) ---
            \Modules\Category\database\seeders\CategoryDatabaseSeeder::class,
            \Modules\Brand\database\seeders\BrandDatabaseSeeder::class,
            \Modules\Product\database\seeders\AttributeDatabaseSeeder::class,
            
            // ProductSeeder tạo sản phẩm + biến thể
            \Modules\Product\database\seeders\ProductDatabaseSeeder::class,
            \Modules\Collection\database\seeders\CollectionDatabaseSeeder::class,

            // --- 4. OPERATIONS (Vận hành) ---
            // Khởi tạo tồn kho (Zero hoặc số lượng đầu kỳ)
            \Modules\Inventory\database\seeders\InventoryDatabaseSeeder::class,

            // --- 5. MARKETING ---
            \Modules\Voucher\database\seeders\VoucherDatabaseSeeder::class,
            \Modules\Promotion\database\seeders\PromotionDatabaseSeeder::class,

            // --- 6. TRANSACTIONS (Giao dịch) ---
            // Quan trọng: OrderSeeder (bản mới) sẽ tự động tạo luôn Shipping bên trong nó
            // để đảm bảo logic thời gian và trạng thái khớp nhau.
            \Modules\Order\database\seeders\OrderDatabaseSeeder::class,
            
            // --- 7. POST-SALES (Sau bán hàng) ---
            // \Modules\Payment\database\seeders\PaymentDatabaseSeeder::class,
            \Modules\Review\database\seeders\ReviewDatabaseSeeder::class,
            \Modules\Wishlist\database\seeders\WishlistDatabaseSeeder::class,
        ]);
    }
}