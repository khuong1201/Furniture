<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('carts', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            // Một user chỉ có 1 cart active tại 1 thời điểm
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete()->unique();
            $table->string('voucher_code')->nullable();
            $table->unsignedBigInteger('voucher_discount')->default(0);
            $table->timestamps();
        });

        Schema::create('cart_items', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('cart_id')->constrained('carts')->cascadeOnDelete();
            
            $table->foreignId('product_variant_id')->constrained('product_variants')->cascadeOnDelete();
            
            $table->integer('quantity')->default(1);
            $table->unsignedBigInteger('price')->default(0);
            
            $table->timestamps();

            $table->unique(['cart_id', 'product_variant_id']); 
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cart_items');
        Schema::dropIfExists('carts');
    }
};