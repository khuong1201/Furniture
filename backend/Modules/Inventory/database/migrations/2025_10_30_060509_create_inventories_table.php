<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('inventory_stocks', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            
            $table->foreignId('warehouse_id')->constrained('warehouses')->cascadeOnDelete();
            
            $table->foreignId('product_variant_id')->constrained('product_variants')->cascadeOnDelete();
            
            $table->integer('quantity')->default(0);
            $table->integer('min_threshold')->default(0); 
            
            $table->timestamps();

            $table->unique(['warehouse_id', 'product_variant_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inventory_stocks');
    }
};