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
            
            // percentage: giảm %, fixed: giảm số tiền cố định
            $table->enum('type', ['percentage', 'fixed'])->default('percentage'); 
            $table->decimal('value', 12, 2); // Giá trị giảm (vd: 10% hoặc 50.000)
            
            $table->decimal('min_order_value', 12, 2)->nullable()->comment('Giá trị đơn hàng tối thiểu'); 
            $table->decimal('max_discount_amount', 12, 2)->nullable()->comment('Giảm tối đa bao nhiêu (cho loại %)');
            
            $table->integer('quantity')->default(0)->comment('Tổng số lượng mã (0 = không giới hạn)'); 
            $table->integer('used_count')->default(0); 
            
            $table->integer('limit_per_user')->default(1); 
            
            $table->timestamp('start_date')->index();
            $table->timestamp('end_date')->index();
            $table->boolean('is_active')->default(true)->index(); // Chuẩn hóa thành is_active
            
            $table->softDeletes();
            $table->timestamps();
        });

        Schema::create('product_promotion', function (Blueprint $table) {
            $table->foreignId('promotion_id')->constrained('promotions')->cascadeOnDelete();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            
            $table->primary(['promotion_id', 'product_id']); 
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_promotion');
        Schema::dropIfExists('promotions');
    }
};