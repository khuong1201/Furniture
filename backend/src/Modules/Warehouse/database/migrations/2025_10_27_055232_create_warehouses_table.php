<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('warehouses', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->string('name', 150)->unique(); // Name should be unique
            $table->string('location', 255)->nullable();
            
            // Manager có thể null (kho chưa có quản lý)
            $table->foreignId('manager_id')->nullable()->constrained('users')->nullOnDelete();
            
            $table->boolean('is_active')->default(true); // Thêm trạng thái hoạt động
            
            $table->softDeletes();
            $table->timestamps();
            
            $table->index('manager_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('warehouses');
    }
};