<?php

namespace Modules\Review\tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\User\Domain\Models\User;
use Modules\Product\Domain\Models\Product;
use Modules\Review\Domain\Models\Review;
use Modules\Auth\Http\Middleware\JwtAuthenticate;

class ReviewTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_create_review()
    {
        // Bỏ qua middleware JwtAuthenticate để tránh lỗi 401
        $this->withoutMiddleware(JwtAuthenticate::class);

        // Tạo user và product
        $user = User::factory()->create();
        $product = Product::factory()->create();

        // Giả lập đăng nhập
        $this->actingAs($user, 'api'); // hoặc 'web' tùy guard bạn dùng

        // Gửi request tạo review
        $response = $this->postJson('/api/reviews', [
            'product_id' => $product->id,
            'rating' => 5,
            'comment' => 'Excellent product!',
        ]);

        // Kiểm tra kết quả
        $response->assertStatus(201);
        $this->assertDatabaseHas('reviews', [
            'product_id' => $product->id,
            'user_id' => $user->id,
            'comment' => 'Excellent product!',
        ]);
    }
}
