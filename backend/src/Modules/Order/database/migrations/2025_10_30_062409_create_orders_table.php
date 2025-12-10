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
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();

            $table->json('shipping_address_snapshot')->nullable(); 
            
            $table->string('status', 50)->default('pending')->index();
            $table->string('payment_status', 50)->default('unpaid')->index();
            $table->string('shipping_status', 50)->default('not_shipped');
            
            $table->unsignedBigInteger('total_amount')->default(0);
            $table->text('notes')->nullable();
            
            $table->string('voucher_code')->nullable();
            $table->unsignedBigInteger('voucher_discount')->default(0);

            $table->timestamp('ordered_at')->useCurrent();
            $table->softDeletes();
            $table->timestamps();
        });

        Schema::create('order_items', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('order_id')->constrained('orders')->cascadeOnDelete();

            $table->foreignId('product_variant_id')->constrained('product_variants'); // Không cascade delete variant để giữ lịch sử
            $table->foreignId('warehouse_id')->constrained('warehouses');
            
            $table->integer('quantity');

            $table->unsignedBigInteger('original_price');
            $table->unsignedBigInteger('discount_amount')->default(0);
            $table->unsignedBigInteger('unit_price');
            $table->unsignedBigInteger('subtotal');
            
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