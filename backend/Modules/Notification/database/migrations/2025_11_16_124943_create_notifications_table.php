<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notifications', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            
            $table->string('title');
            $table->text('content');
            $table->enum('type', ['info', 'success', 'warning', 'error'])->default('info');

            $table->json('data')->nullable(); 
            
            $table->timestamp('read_at')->nullable();
            
            $table->timestamps();
            $table->softDeletes();

            $table->index(['user_id', 'read_at']); 
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notifications');
    }
};