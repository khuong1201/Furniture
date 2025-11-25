<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('inventories', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->foreignId('warehouse_id')->constrained('warehouses')->cascadeOnDelete();
            $table->integer('stock_quantity')->default(0);
            $table->integer('min_threshold')->default(0);
            $table->integer('max_threshold')->nullable();
            $table->enum('status', ['in_stock','low_stock','out_of_stock'])->default('in_stock');
            $table->softDeletes();
            $table->timestamps();

            $table->unique(['product_id','warehouse_id']);
            $table->index(['product_id','warehouse_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inventories');
    }
};
