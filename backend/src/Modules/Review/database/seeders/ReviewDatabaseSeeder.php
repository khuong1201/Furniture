<?php

namespace Modules\Review\database\seeders;

use Illuminate\Database\Seeder;
use Modules\Review\Domain\Models\Review;
use Modules\Product\Domain\Models\Product;
use Modules\User\Domain\Models\User;
use Illuminate\Support\Str;

class ReviewDatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Xóa dữ liệu cũ để tránh trùng lặp nếu chạy nhiều lần
        Review::truncate();

        // Lấy danh sách khách hàng và sản phẩm
        $customers = User::whereHas('roles', fn($q) => $q->where('name', 'customer'))->get();
        $products = Product::all();

        if ($customers->isEmpty() || $products->isEmpty()) {
            return;
        }

        foreach ($products as $product) {
            // Random số lượng đánh giá cho mỗi sản phẩm (từ 0 đến 8 đánh giá)
            $reviewCount = rand(0, 8);
            
            if ($reviewCount > 0) {
                // Lấy ngẫu nhiên user từ danh sách để đánh giá
                $randomCustomers = $customers->random(min($reviewCount, $customers->count()));

                foreach ($randomCustomers as $customer) {
                    $rating = $this->getWeightedRating(); 
                    
                    // Thời gian review: Random trong 6 tháng qua
                    $createdAt = now()->subDays(rand(1, 180))->subHours(rand(1, 24));

                    Review::create([
                        'uuid' => (string) Str::uuid(),
                        'user_id' => $customer->id,
                        'product_id' => $product->id,
                        'order_id' => null,
                        'rating' => $rating,
                        'comment' => $this->generateFurnitureComment($rating, $product->name),
                        'is_approved' => true,
                        'images' => $this->generateReviewImages($rating),
                        'created_at' => $createdAt,
                        'updated_at' => $createdAt
                    ]);
                }
                
                // Cập nhật thống kê rating cho sản phẩm
                $this->updateProductStats($product);
            }
        }
    }

    protected function getWeightedRating(): int
    {
        // Tỷ lệ rating thực tế: Ít khi 1-2 sao, chủ yếu 4-5 sao hoặc 3 sao
        $rand = rand(1, 100);
        if ($rand <= 5) return 1;
        if ($rand <= 10) return 2;
        if ($rand <= 25) return 3;
        if ($rand <= 60) return 4;
        return 5;
    }

    protected function generateFurnitureComment(int $rating, string $productName): string
    {
        $comments = [
            5 => [
                "Sản phẩm tuyệt vời, đúng như mô tả. Gỗ rất chắc chắn.",
                "Giao hàng siêu nhanh, đóng gói kỹ. Sofa ngồi êm lắm.",
                "Rất hài lòng với chiếc {$productName} này. Sẽ ủng hộ shop tiếp.",
                "Màu sắc bên ngoài đẹp hơn trong ảnh. Lắp ráp cũng dễ.",
                "10 điểm cho chất lượng! Nhân viên tư vấn nhiệt tình."
            ],
            4 => [
                "Sản phẩm ổn trong tầm giá, giao hàng hơi lâu xíu.",
                "Đẹp, nhưng màu hơi khác ảnh một chút xíu.",
                "Chất lượng tốt, nhưng hướng dẫn lắp ráp hơi khó hiểu.",
                "Dùng được 1 tuần thấy khá ổn, chưa thấy lỗi gì.",
                "Hàng đẹp, đóng gói cẩn thận. Trừ 1 điểm vì ship chậm."
            ],
            3 => [
                "Tạm được. Gỗ hơi mỏng so với tưởng tượng.",
                "Giao hàng lâu quá, chờ mãi mới thấy.",
                "Sản phẩm bình thường, không có gì đặc sắc.",
                "Hoàn thiện ở các góc cạnh chưa được tinh xảo lắm.",
                "Dùng tạm ổn, nhưng giá này hơi cao so với chất lượng."
            ],
            2 => [
                "Không hài lòng lắm. Đệm ngồi hơi cứng.",
                "Mới dùng vài ngày đã thấy chân ghế hơi lung lay.",
                "Màu sắc không giống hình, thất vọng.",
                "Giao thiếu ốc vít, phải chạy đi mua thêm.",
                "Chăm sóc khách hàng kém, hỏi mãi không trả lời."
            ],
            1 => [
                "Quá tệ! Hàng bị trầy xước tùm lum khi nhận.",
                "Treo đầu dê bán thịt chó. Đừng mua!",
                "Đặt màu xanh giao màu đỏ. Làm ăn chán quá.",
                "Chất lượng quá kém, ọp ẹp như hàng mã.",
                "Yêu cầu đổi trả mà shop không chịu. Cạch mặt."
            ]
        ];

        // FIX: Chọn ngẫu nhiên 1 comment từ mảng dựa trên rating và trả về
        $list = $comments[$rating] ?? $comments[5]; // Fallback về 5 sao nếu lỗi
        return $list[array_rand($list)];
    }

    protected function generateReviewImages(int $rating): ?array
    {
        // 20% review 4-5 sao sẽ có ảnh
        if ($rating >= 4 && rand(1, 100) <= 20) {
            return [
                "https://placehold.co/400x400?text=Review+1",
                "https://placehold.co/400x400?text=Review+2"
            ];
        }
        return null;
    }

    protected function updateProductStats(Product $product): void
    {
        $avg = $product->reviews()->where('is_approved', true)->avg('rating');
        $count = $product->reviews()->where('is_approved', true)->count();

        $product->update([
            'rating_avg' => $avg ? round((float)$avg, 1) : 0,
            'rating_count' => $count
        ]);
    }
}