<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // 1. Attributes
        Schema::create('attributes', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->string('name'); 
            $table->string('slug')->unique(); 
            $table->string('type')->default('select'); 
            $table->timestamps();
        });

        // 2. Attribute Values
        Schema::create('attribute_values', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('attribute_id')->constrained()->cascadeOnDelete();
            $table->string('value'); 
            $table->string('code')->nullable();
            $table->timestamps();
        });

        // 3. Products
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->string('name', 150)->index();
            $table->text('description')->nullable();
            $table->foreignId('category_id')->nullable()->constrained('categories')->nullOnDelete();
            
            $table->boolean('has_variants')->default(false);
            $table->boolean('is_active')->default(true)->index();

            // Cached stats
            $table->unsignedBigInteger('sold_count')->default(0)->index();
            $table->decimal('rating_avg', 3, 2)->default(0)->index(); 
            $table->unsignedInteger('rating_count')->default(0);
            
            // Simple product fields
            $table->unsignedBigInteger('price')->nullable()->comment('Min price of variants (Base Currency)');
            $table->string('sku', 100)->unique()->nullable();
            
            $table->softDeletes();
            $table->timestamps();
        });

        // 4. Product Images
        Schema::create('product_images', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->string('image_url');
            $table->string('public_id')->nullable()->comment('ID storage để xóa ảnh');
            $table->boolean('is_primary')->default(false);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });

        // 5. Product Variants
        Schema::create('product_variants', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            
            $table->string('sku', 100)->unique();
            $table->unsignedBigInteger('price')->comment('Base Currency');
            $table->decimal('weight', 10, 2)->nullable();
            
            $table->string('image_url')->nullable(); 
            $table->unsignedBigInteger('sold_count')->default(0)->index();
            
            $table->timestamps();
        });

        // 6. Variant - Attributes Pivot
        Schema::create('variant_attribute_values', function (Blueprint $table) {
            $table->foreignId('product_variant_id')->constrained('product_variants')->cascadeOnDelete();
            $table->foreignId('attribute_value_id')->constrained('attribute_values')->cascadeOnDelete();
            $table->primary(['product_variant_id', 'attribute_value_id'], 'var_attr_pk');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('variant_attribute_values');
        Schema::dropIfExists('product_variants');
        Schema::dropIfExists('product_images');
        Schema::dropIfExists('products');
        Schema::dropIfExists('attribute_values');
        Schema::dropIfExists('attributes');
    }
};