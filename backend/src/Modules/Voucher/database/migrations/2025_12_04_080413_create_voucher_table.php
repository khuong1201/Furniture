<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vouchers', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->string('code', 50)->unique()->index(); // Index for fast lookup
            $table->string('name');
            $table->text('description')->nullable();
            
            $table->enum('type', ['fixed', 'percentage'])->default('fixed'); 
            $table->unsignedBigInteger('value'); 
            
            $table->unsignedBigInteger('min_order_value')->nullable(); // [CHANGE]
            $table->unsignedBigInteger('max_discount_amount')->nullable();
            
            $table->integer('quantity')->default(0); 
            $table->integer('used_count')->default(0); 
            
            $table->integer('limit_per_user')->default(1); 
            
            $table->timestamp('start_date')->nullable();
            $table->timestamp('end_date')->nullable();
            $table->boolean('is_active')->default(true);
            
            $table->softDeletes();
            $table->timestamps();
        });

        Schema::create('voucher_usages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('voucher_id')->constrained('vouchers')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('order_id')->constrained('orders')->cascadeOnDelete();
            $table->unsignedBigInteger('discount_amount');
            $table->timestamp('used_at')->useCurrent();
            
            $table->index(['user_id', 'voucher_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('voucher_usages');
        Schema::dropIfExists('vouchers');
    }
};