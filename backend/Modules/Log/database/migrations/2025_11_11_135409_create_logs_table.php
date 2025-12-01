<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('logs', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->unsignedBigInteger('user_id')->nullable()->index();
         
            $table->string('type', 50)->index();
            
            $table->string('action', 100)->index();
                      
            $table->string('model', 150)->nullable()->index();
            $table->uuid('model_uuid')->nullable()->index();
            
            $table->ipAddress('ip_address')->nullable();
            $table->text('message')->nullable(); 
            $table->json('metadata')->nullable();
            
            $table->timestamps();
            
            $table->index(['model', 'model_uuid']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('logs');
    }
};