<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('refresh_tokens', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            
            $table->string('token', 64)->unique(); 
            $table->string('device_name')->nullable();
            $table->string('ip', 45)->nullable(); 
            $table->text('user_agent')->nullable();
            $table->timestamp('expires_at')->index();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('refresh_tokens');
    }
};