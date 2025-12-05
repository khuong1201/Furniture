<?php

namespace Modules\Review\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Review\Domain\Models\Review;
use Modules\Product\Domain\Models\Product;
use Modules\User\Domain\Models\User;
use Illuminate\Support\Str;

class ReviewDatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Xóa dữ liệu cũ
        Review::truncate();

        // Lấy danh sách khách hàng và sản phẩm
        $customers = User::whereHas('roles', fn($q) => $q->where('name', 'customer'))->get();
        $products = Product::all();

        if ($customers->isEmpty() || $products->isEmpty()) {
            $this->command->info('Skipping Review Seeder: No customers or products found.');
            return;
        }

        foreach ($products as $product) {
            // Random số lượng đánh giá cho mỗi sản phẩm (từ 2 đến 10 đánh giá)
            $reviewCount = rand(2, 10);
            
            // Lấy ngẫu nhiên user để đánh giá (tránh 1 user review 2 lần 1 sp)
            $randomCustomers = $customers->shuffle()->take($reviewCount);

            foreach ($randomCustomers as $customer) {
                $rating = $this->getWeightedRating(); // Random sao theo trọng số
                
                Review::create([
                    'uuid' => Str::uuid(),
                    'user_id' => $customer->id,
                    'product_id' => $product->id,
                    'order_id' => null, // Seeder đơn giản, bỏ qua check order_id
                    'rating' => $rating,
                    'comment' => $this->generateFurnitureComment($rating, $product->name),
                    'is_approved' => true, // Auto duyệt để hiện lên UI ngay
                    'images' => $this->generateReviewImages($rating),
                    'created_at' => now()->subDays(rand(1, 30)) // Rải rác trong 30 ngày qua
                ]);
            }

            // Cập nhật lại thống kê cho Product (Quan trọng để hiện sao trung bình)
            $this->updateProductStats($product);
        }
    }

    /**
     * Random số sao, ưu tiên 4-5 sao để data đẹp
     */
    protected function getWeightedRating(): int
    {
        $rand = rand(1, 100);
        if ($rand <= 5) return 1;  // 5% cơ hội 1 sao
        if ($rand <= 10) return 2; // 5% cơ hội 2 sao
        if ($rand <= 20) return 3; // 10% cơ hội 3 sao
        if ($rand <= 50) return 4; // 30% cơ hội 4 sao
        return 5;                  // 50% cơ hội 5 sao
    }

    /**
     * Tạo nội dung review phù hợp với nội thất
     */
    protected function generateFurnitureComment(int $rating, string $productName): string
    {
        $good = [
            "Sản phẩm {$productName} rất đẹp, chất gỗ chắc chắn.",
            "Giao hàng nhanh, đóng gói kỹ. Sofa ngồi rất êm.",
            "Màu sắc y hệt trong hình, rất hợp với phòng khách nhà mình.",
            "Tuyệt vời! Lắp ráp dễ dàng, nhìn rất sang trọng.",
            "Đáng đồng tiền bát gạo, sẽ ủng hộ shop tiếp."
        ];

        $average = [
            "Sản phẩm tạm ổn trong tầm giá.",
            "Giao hàng hơi chậm nhưng {$productName} chất lượng ok.",
            "Màu sắc hơi nhạt hơn so với ảnh một chút.",
            "Dùng được, nhưng phần đệm hơi cứng.",
            "Hoàn thiện chưa được tinh xảo lắm ở các góc cạnh."
        ];

        $bad = [
            "Thất vọng. Gỗ bị trầy xước khi nhận hàng.",
            "Không giống mô tả, chất liệu vải rất nóng.",
            "Giao sai màu, nhắn tin shop trả lời chậm.",
            "Chân ghế bị lung lay, không chắc chắn.",
            "Quá tệ, không bao giờ mua lại."
        ];

        if ($rating >= 4) return $good[array_rand($good)];
        if ($rating == 3) return $average[array_rand($average)];
        return $bad[array_rand($bad)];
    }

    protected function generateReviewImages(int $rating): ?array
    {
        // Chỉ 30% review có ảnh, và chỉ review tốt mới hay chụp ảnh khoe
        if ($rating >= 4 && rand(0, 1)) {
            return [
                "https://placehold.co/400x400?text=Review+Img+1",
                "https://placehold.co/400x400?text=Review+Img+2"
            ];
        }
        return null;
    }

    protected function updateProductStats(Product $product): void
    {
        // Tính toán lại
        $avg = $product->reviews()->where('is_approved', true)->avg('rating');
        $count = $product->reviews()->where('is_approved', true)->count();

        // Lưu vào bảng products
        $product->update([
            'rating_avg' => round((float)$avg, 1),
            'rating_count' => $count
        ]);
    }
}