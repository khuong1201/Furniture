<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reviews', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->foreignId('order_id')->nullable()->constrained('orders')->nullOnDelete(); // Verify purchase
            
            $table->unsignedTinyInteger('rating')->comment('1-5 stars');
            $table->text('comment')->nullable();
            $table->json('images')->nullable(); // Lưu danh sách URL ảnh review
            
            $table->boolean('is_approved')->default(false); // Cần duyệt trước khi hiện
            
            $table->softDeletes();
            $table->timestamps();

            // Mỗi user chỉ được review 1 lần cho 1 sản phẩm (hoặc 1 lần/1 đơn hàng tùy logic, ở đây theo unique constraint cũ là 1 user/1 product)
            $table->unique(['user_id', 'product_id']);
            $table->index(['product_id', 'is_approved']); // Index để query hiển thị nhanh
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reviews');
    }
};