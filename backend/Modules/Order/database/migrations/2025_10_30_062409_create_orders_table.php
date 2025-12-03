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
            
            $table->enum('status', ['pending', 'processing', 'shipped', 'delivered', 'cancelled'])->default('pending');
            $table->enum('payment_status', ['unpaid', 'paid', 'refunded'])->default('unpaid');
            $table->enum('shipping_status', ['not_shipped', 'shipped', 'delivered'])->default('not_shipped');
            
            $table->decimal('total_amount', 12, 2)->default(0);
            
            $table->text('notes')->nullable();
            
            $table->timestamp('ordered_at')->useCurrent();
            $table->softDeletes();
            $table->timestamps();

            $table->index(['status', 'user_id']);
        });

        Schema::create('order_items', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('order_id')->constrained('orders')->cascadeOnDelete();

            $table->foreignId('product_variant_id')->constrained('product_variants')->cascadeOnDelete();

            $table->foreignId('warehouse_id')->constrained('warehouses')->restrictOnDelete(); 
            
            $table->integer('quantity');

            $table->decimal('original_price', 12, 2)->comment('Giá gốc của biến thể');
            $table->decimal('discount_amount', 12, 2)->default(0);
            $table->decimal('unit_price', 12, 2)->comment('Giá sau giảm');
            $table->decimal('subtotal', 12, 2); 
            
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