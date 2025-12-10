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
            
            $table->string('provider'); // GHN, GHTK, ViettelPost...
            $table->string('tracking_number')->unique()->nullable(); // Có thể null lúc mới tạo

            $table->enum('status', ['pending', 'shipped', 'delivered', 'returned', 'cancelled'])->default('pending');

            $table->unsignedBigInteger('fee')->default(0);
            
            $table->timestamp('shipped_at')->nullable();
            $table->timestamp('delivered_at')->nullable();
            
            $table->softDeletes();
            $table->timestamps();
            
            // Index để tìm kiếm nhanh theo tracking number
            $table->index('tracking_number');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('shippings');
    }
};