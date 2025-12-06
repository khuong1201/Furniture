<?php

namespace Modules\Promotion\database\seeders;

use Illuminate\Database\Seeder;
use Modules\Promotion\Domain\Models\Promotion;
use Modules\Product\Domain\Models\Product;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class PromotionDatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Kiểm tra Product
        if (Product::count() === 0) {
            $this->command->warn('⚠ Chưa có Product nào. Hãy chạy ProductDatabaseSeeder trước!');
            return;
        }

        // 2. CLEANUP: Xóa các Promotion test cũ để tránh rác DB
        Promotion::where('name', 'SIÊU SALE GIẢM 50%')->delete();

        // 3. TẠO FLASH SALE MỚI
        $flashSale = Promotion::create([
            'uuid' => (string) Str::uuid(),
            'name' => 'SIÊU SALE GIẢM 50%',
            'type' => 'percentage',
            'value' => 50, // Giảm 50%
            'start_date' => now()->subDay(), // Bắt đầu từ hôm qua
            'end_date' => now()->addDays(7), // Kết thúc sau 7 ngày
            'is_active' => true,
            'quantity' => 0, // Không giới hạn
            'max_discount_amount' => 500000,
        ]);

        // 4. LẤY 5 SẢN PHẨM & GẮN VÀO
        $randomProducts = Product::inRandomOrder()->limit(5)->get();
        
        foreach ($randomProducts as $product) {
            // Quan trọng: Gỡ bỏ các promotion cũ của sp này (nếu có) để tránh xung đột
            $product->promotions()->detach();
            
            // Gắn vào Flash Sale mới
            $product->promotions()->attach($flashSale->id);
        }

        $this->command->info("✅ Đã tạo Flash Sale 50% và gắn vào " . $randomProducts->count() . " sản phẩm.");
        $this->command->info("👉 UUID các sản phẩm có Sale: " . $randomProducts->pluck('uuid')->join(', '));

        // 5. Tạo thêm data rác (Optional)
        // Promotion::factory()->count(2)->create(); 
    }
}