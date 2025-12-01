<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('addresses', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            
            $table->string('full_name', 100);
            $table->string('phone', 20);

            $table->string('province', 100);
            $table->string('district', 100);
            $table->string('ward', 100);
            $table->string('street', 255);
            
            $table->boolean('is_default')->default(false);
            
            $table->softDeletes();
            $table->timestamps();

            $table->index(['user_id', 'is_default']); 
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('addresses');
    }
};