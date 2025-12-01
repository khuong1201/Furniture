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
            $table->enum('type', ['percentage', 'fixed']); 
            $table->decimal('value', 12, 2);
            
            $table->dateTime('start_date')->index(); 
            $table->dateTime('end_date')->index();
            $table->boolean('status')->default(true); 
            
            $table->softDeletes();
            $table->timestamps();
            
            $table->index(['status', 'start_date', 'end_date']);
        });

        Schema::create('promotion_product', function (Blueprint $table) {
            $table->foreignId('promotion_id')->constrained('promotions')->cascadeOnDelete();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            
            $table->primary(['promotion_id', 'product_id']); 
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('promotion_product');
        Schema::dropIfExists('promotions');
    }
};