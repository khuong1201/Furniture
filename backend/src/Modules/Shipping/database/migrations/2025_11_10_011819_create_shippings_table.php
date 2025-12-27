<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('shippings', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            
            $table->foreignId('order_id')->constrained('orders')->cascadeOnDelete();
            
            // --- THÊM MỚI 3 CỘT NÀY ---
            $table->string('consignee_name')->nullable();
            $table->string('consignee_phone')->nullable();
            $table->text('address_full')->nullable();

            // --- SỬA CỘT NÀY ---
            // Phải cho nullable vì đơn Pending chưa có Provider
            $table->string('provider')->nullable(); 
            
            $table->string('tracking_number')->unique()->nullable(); 

            $table->enum('status', ['pending', 'shipped', 'delivered', 'returned', 'cancelled'])->default('pending');

            $table->unsignedBigInteger('fee')->default(0);
            
            $table->timestamp('shipped_at')->nullable();
            $table->timestamp('delivered_at')->nullable();
            
            $table->softDeletes();
            $table->timestamps();
            
            $table->index('tracking_number');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('shippings');
    }
};