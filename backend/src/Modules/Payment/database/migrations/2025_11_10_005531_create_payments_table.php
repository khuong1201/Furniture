<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('order_id')->constrained('orders')->cascadeOnDelete();
            
            $table->string('method', 50); // cod, momo, vnpay...
            $table->unsignedBigInteger('amount'); 
            $table->string('currency', 3)->default('VND');
            
            $table->string('status', 20)->default('pending')->index(); // pending, paid, failed, refunded
            $table->timestamp('paid_at')->nullable();
            
            $table->string('transaction_id')->nullable()->comment('Mã giao dịch từ cổng thanh toán');
            $table->json('payment_data')->nullable()->comment('Metadata từ cổng thanh toán');
            
            $table->softDeletes();
            $table->timestamps();
            
            $table->index(['order_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};