<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('promotions', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->string('name');
            $table->text('description')->nullable();
            
            // Type: percentage (giảm %), fixed (giảm số tiền cố định)
            $table->enum('type', ['percentage', 'fixed'])->default('percentage'); 
            
            // CHANGE: Decimal -> UnsignedBigInteger
            // Lưu ý: Nếu type=percentage, value=10 nghĩa là 10%. 
            // Nếu type=fixed, value=50000 nghĩa là 50,000 VND.
            $table->unsignedBigInteger('value'); 
            
            // CHANGE: Decimal -> UnsignedBigInteger
            $table->unsignedBigInteger('min_order_value')->nullable()->comment('Giá trị đơn hàng tối thiểu'); 
            $table->unsignedBigInteger('max_discount_amount')->nullable()->comment('Giảm tối đa bao nhiêu (cho loại %)');
            
            $table->integer('quantity')->default(0)->comment('Tổng số lượng mã (0 = không giới hạn)'); 
            $table->integer('used_count')->default(0); 
            $table->integer('limit_per_user')->default(1); 
            
            // Indexing for performance
            $table->timestamp('start_date')->nullable()->index();
            $table->timestamp('end_date')->nullable()->index();
            $table->boolean('is_active')->default(true)->index();
            
            $table->softDeletes();
            $table->timestamps();
        });

        Schema::create('product_promotion', function (Blueprint $table) {
            $table->foreignId('promotion_id')->constrained('promotions')->cascadeOnDelete();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            
            $table->primary(['promotion_id', 'product_id']);
            // Index ngược để tìm promotion từ product nhanh hơn
            $table->index('product_id'); 
            $table->timestamps();
            
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_promotion');
        Schema::dropIfExists('promotions');
    }
};