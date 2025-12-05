<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('roles', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->string('name', 100)->unique();
            $table->string('slug', 100)->unique(); 
            $table->text('description')->nullable();
            $table->string('guard_name', 50)->default('web');
            $table->boolean('is_system')->default(false)->comment('Role hệ thống không được xóa');
            $table->unsignedInteger('priority')->default(0);
            $table->timestamps();
            $table->softDeletes();

            $table->index(['guard_name', 'is_system']);
        });

        Schema::create('role_user', function (Blueprint $table) {
            $table->foreignId('role_id')->constrained('roles')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->timestamp('assigned_at')->useCurrent();
            $table->foreignId('assigned_by')->nullable()->constrained('users')->nullOnDelete();

            $table->timestamps(); 
            
            $table->primary(['role_id', 'user_id']);
            $table->index('user_id'); 
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('role_user');
        Schema::dropIfExists('roles');
    }
};