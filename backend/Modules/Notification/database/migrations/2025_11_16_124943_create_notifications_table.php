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
            $table->string('type')->default('info'); // info, warning, success, error

            $table->json('data')->nullable(); // Metadata (link, object_id...)
            
            $table->timestamp('read_at')->nullable();
            
            $table->timestamps();
            $table->softDeletes();

            $table->index(['user_id', 'read_at']); 
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notifications');
    }
};