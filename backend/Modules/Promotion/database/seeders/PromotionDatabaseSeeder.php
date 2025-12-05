<?php

namespace Modules\Promotion\database\seeders;

use Illuminate\Database\Seeder;
use Modules\Promotion\Domain\Models\Promotion;
use Modules\Product\Domain\Models\Product;
use Illuminate\Support\Str;

class PromotionDatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Kiểm tra xem có Product nào chưa, nếu chưa thì báo lỗi hoặc tạo fake
        if (Product::count() === 0) {
            $this->command->warn('Chưa có Product nào trong DB. Hãy chạy ProductDatabaseSeeder trước!');
            return;
        }

        // 2. Tạo Flash Sale 50% (Cố định để test)
        $flashSale = Promotion::create([
            'uuid' => (string) Str::uuid(),
            'name' => 'SIÊU SALE GIẢM 50%',
            'type' => 'percentage',
            'value' => 50, // 50%
            'start_date' => now()->subDay(), // Đã bắt đầu từ hôm qua
            'end_date' => now()->addDays(7), // Kết thúc sau 7 ngày
            'is_active' => true,
            'quantity' => 0, // Không giới hạn
            'max_discount_amount' => 500000,
        ]);

        // 3. QUAN TRỌNG: Lấy 5 sản phẩm ngẫu nhiên và GẮN vào Flash Sale này
        // Nếu không có bước này, query lọc sẽ không ra kết quả
        $randomProducts = Product::inRandomOrder()->limit(5)->get();
        
        $flashSale->products()->attach($randomProducts->pluck('id'));

        $this->command->info("Đã tạo Flash Sale 50% và gắn vào " . $randomProducts->count() . " sản phẩm.");

        // 4. Tạo thêm các promotion rác khác (optional)
        Promotion::factory()->count(5)->create();
    }
}