<?php

namespace Modules\Promotion\database\seeders;

use Illuminate\Database\Seeder;
use Modules\Product\Domain\Models\Product;
use Modules\Promotion\Domain\Models\Promotion;
use Illuminate\Support\Str;

class PromotionDatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Tạo các chiến dịch khuyến mãi theo mùa/sự kiện
        $campaigns = [
            [
                'name' => 'Flash Sale Cuối Tuần',
                'description' => 'Giảm giá cực sốc chỉ trong 48h',
                'type' => 'percentage',
                'value' => 20, // Giảm 20%
                'start_date' => now()->subDays(1),
                'end_date' => now()->addDays(2), // Đang diễn ra
                'product_count' => 10, // Áp dụng cho 10 sp
            ],
            [
                'name' => 'Xả Kho Đón Tết',
                'description' => 'Dọn kho giá rẻ như cho',
                'type' => 'percentage',
                'value' => 50, // Giảm 50%
                'start_date' => now()->subMonths(1),
                'end_date' => now()->subDays(10), // Đã kết thúc
                'product_count' => 20,
            ],
            [
                'name' => 'Combo Phòng Ngủ',
                'description' => 'Mua giường tặng đệm',
                'type' => 'fixed',
                'value' => 500000, // Giảm thẳng 500k
                'start_date' => now(),
                'end_date' => now()->addMonths(1), // Sắp tới/Đang diễn ra
                'product_count' => 15,
            ],
            [
                'name' => 'Black Friday Sớm',
                'description' => 'Săn sale sớm nhận quà to',
                'type' => 'percentage',
                'value' => 15,
                'start_date' => now()->addDays(5),
                'end_date' => now()->addDays(10), // Sắp diễn ra
                'product_count' => 30,
            ],
            [
                'name' => 'Ưu Đãi Ghế Văn Phòng',
                'description' => 'Giảm giá đặc biệt cho doanh nghiệp',
                'type' => 'percentage',
                'value' => 10,
                'start_date' => now()->subDays(5),
                'end_date' => now()->addMonths(2), // Đang diễn ra dài hạn
                'product_count' => 12,
            ]
        ];

        $allProducts = Product::all();
        if ($allProducts->isEmpty()) return;

        foreach ($campaigns as $camp) {
            $promotion = Promotion::firstOrCreate(['name' => $camp['name']], [
                'uuid' => (string) Str::uuid(),
                'description' => $camp['description'],
                'type' => $camp['type'],
                'value' => $camp['value'],
                'start_date' => $camp['start_date'],
                'end_date' => $camp['end_date'],
                'is_active' => true,
            ]);

            // Random sản phẩm để gắn vào khuyến mãi
            // take() và shuffle() để lấy ngẫu nhiên ko trùng lặp trong 1 campaign
            $randomProducts = $allProducts->shuffle()->take($camp['product_count']);
            
            // Sync sản phẩm vào promotion (quan hệ n-n)
            // Giả sử relation tên là 'products'
            $promotion->products()->syncWithoutDetaching($randomProducts->pluck('id'));
        }
    }
}