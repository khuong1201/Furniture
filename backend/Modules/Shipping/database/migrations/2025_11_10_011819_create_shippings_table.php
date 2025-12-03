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
            
            $table->string('provider');
            $table->string('tracking_number')->unique(); 

            $table->enum('status', ['pending', 'shipped', 'delivered', 'returned', 'cancelled'])->default('pending');

            $table->decimal('fee', 12, 2)->default(0); 
            
            $table->timestamp('shipped_at')->nullable();
            $table->timestamp('delivered_at')->nullable();
            
            $table->softDeletes();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('shippings');
    }
};