<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('media', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            
            // Morph to (User, Product, etc.)
            $table->nullableMorphs('model'); 
            
            $table->string('collection_name')->default('default')->index();
            $table->string('file_name');
            $table->string('mime_type')->nullable(); 
            $table->string('disk')->default('cloudinary'); 
            $table->unsignedBigInteger('size'); 
            
            $table->string('url', 512); 
            $table->string('public_id')->nullable(); 
            
            $table->json('custom_properties')->nullable(); 
            
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('media');
    }
};