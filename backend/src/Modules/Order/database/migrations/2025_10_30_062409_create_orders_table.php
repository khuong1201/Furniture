<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->string('code', 32)->nullable()->unique()->index();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();

            // --- FIX: Thêm 2 cột này để khớp với OrderService ---
            // Để ShippingModule lấy thông tin người nhận nhanh mà không cần parse JSON
            $table->string('shipping_name')->nullable();
            $table->string('shipping_phone', 20)->nullable();
            
            // Lưu full address (Tỉnh, Huyện, Xã, Số nhà) để tính phí ship sau này
            $table->json('shipping_address_snapshot')->nullable(); 
            
            $table->string('status', 50)->default('pending')->index();
            $table->string('payment_status', 50)->default('unpaid')->index();
            $table->string('shipping_status', 50)->default('not_shipped');

            $table->text('notes')->nullable();
            
            $table->string('voucher_code')->nullable();
            $table->unsignedBigInteger('voucher_discount')->default(0);

            // Tiền tệ VNĐ nên dùng Integer là tốt nhất
            $table->unsignedBigInteger('subtotal')->default(0);
            $table->unsignedBigInteger('shipping_fee')->default(0);
            $table->unsignedBigInteger('grand_total')->default(0);

            $table->timestamp('ordered_at')->useCurrent();
            $table->softDeletes();
            $table->timestamps();
        });

        Schema::create('order_items', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('order_id')->constrained('orders')->cascadeOnDelete();
            
            // Constrained vào variants để trừ kho
            $table->foreignId('product_variant_id')->constrained('product_variants');
            $table->foreignId('warehouse_id')->constrained('warehouses');
            
            $table->integer('quantity');

            $table->unsignedBigInteger('original_price');
            $table->unsignedBigInteger('discount_amount')->default(0);
            $table->unsignedBigInteger('unit_price');
            $table->unsignedBigInteger('subtotal');
            
            // Lưu tên SP, ảnh, SKU lúc mua để nếu SP bị xóa/sửa thì đơn hàng ko lỗi
            $table->json('product_snapshot')->nullable(); 

            $table->softDeletes();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_items');
        Schema::dropIfExists('orders');
    }
};