<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // 1. Products Table
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->string('name', 150)->index(); 
            $table->text('description')->nullable();
            $table->decimal('price', 12, 2); 
            $table->foreignId('category_id')->nullable()->constrained('categories')->nullOnDelete();
            $table->string('sku', 100)->unique();
            
            $table->decimal('weight', 10, 2)->nullable();
            $table->string('dimensions', 100)->nullable();
            $table->string('material', 100)->nullable();
            $table->string('color', 50)->nullable();
            
            $table->boolean('status')->default(true)->index();
            $table->softDeletes();
            $table->timestamps();
        });

        Schema::create('product_images', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->string('image_url');
            $table->string('public_id')->nullable();
            $table->boolean('is_primary')->default(false);
            $table->timestamps();
            
            $table->index(['product_id', 'is_primary']);
        });

        Schema::create('warehouse_product', function (Blueprint $table) {
            $table->foreignId('warehouse_id')->constrained('warehouses')->cascadeOnDelete();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->integer('quantity')->default(0);
            $table->timestamps();
            
            $table->primary(['warehouse_id', 'product_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('warehouse_product');
        Schema::dropIfExists('product_images');
        Schema::dropIfExists('products');
    }
};