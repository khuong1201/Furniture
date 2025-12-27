<?php

namespace Modules\Product\database\seeders;

use Illuminate\Database\Seeder;
use Modules\Product\Domain\Models\Product;
use Modules\Product\Domain\Models\Attribute;
use Modules\Category\Domain\Models\Category;
use Modules\Brand\Domain\Models\Brand;

class ProductDatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Đảm bảo Attribute tồn tại
        if (Attribute::count() == 0) {
            $this->call(AttributeDatabaseSeeder::class);
        }

        // 2. Lấy danh sách Brand THẬT từ DB (đã được tạo ở bước trước)
        $brands = Brand::all();

        // Fallback: Nếu lỡ chưa chạy BrandSeeder thì tạo 1 cái Generic để cứu code không bị lỗi
        if ($brands->isEmpty()) {
            $this->command->warn('⚠️ No brands found. Creating a generic one.');
            $brands = collect([
                Brand::firstOrCreate(['slug' => 'generic'], ['name' => 'Generic Home', 'is_active' => true])
            ]);
        }

        // 3. Lấy Categories
        $categories = Category::whereNotNull('parent_id')->get();
        if ($categories->isEmpty()) {
            $this->command->warn('⚠️ No sub-categories found. Please run CategorySeeder first.');
            return;
        }

        foreach ($categories as $category) {
            Product::factory()
                ->count(rand(10, 20))
                ->withVariants(rand(1, 3)) 
                ->create([
                    'category_id' => $category->id,
                    
                    // Kỹ thuật: Dùng Closure (hàm ẩn danh) để mỗi sản phẩm trong 
                    // batch này lấy ngẫu nhiên 1 brand khác nhau.
                    'brand_id' => fn() => $brands->random()->id,
                ]);
        }
    }
}