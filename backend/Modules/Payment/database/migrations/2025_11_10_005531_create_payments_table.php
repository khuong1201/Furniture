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
            
            $table->string('method'); 
            $table->decimal('amount', 12, 2); 
            $table->string('currency', 3)->default('VND');
            
            $table->enum('status', ['pending', 'paid', 'failed', 'refunded'])->default('pending');
            $table->timestamp('paid_at')->nullable();
            
            $table->string('transaction_id')->nullable();
            $table->json('payment_data')->nullable();
            
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