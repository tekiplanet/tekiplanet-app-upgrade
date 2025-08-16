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
        Schema::create('user_files', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('sender_id');
            $table->uuid('receiver_id');
            $table->uuid('category_id');
            $table->string('file_name', 255);
            $table->string('original_name', 255);
            $table->bigInteger('file_size'); // in bytes
            $table->string('mime_type', 100);
            $table->string('file_extension', 20);
            
            // Cloudinary fields
            $table->string('cloudinary_public_id', 255);
            $table->string('cloudinary_url', 500);
            $table->string('cloudinary_secure_url', 500);
            $table->enum('resource_type', ['image', 'video', 'raw'])->default('raw');
            
            // File status and metadata
            $table->enum('status', ['active', 'deleted', 'expired'])->default('active');
            $table->integer('download_count')->default(0);
            $table->timestamp('expires_at')->nullable();
            $table->boolean('is_public')->default(false);
            
            // Additional metadata
            $table->json('metadata')->nullable(); // For additional file info
            $table->text('description')->nullable();
            
            $table->timestamps();
            
            // Foreign keys
            $table->foreign('sender_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('receiver_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('category_id')->references('id')->on('file_categories')->onDelete('restrict');
            
            // Indexes
            $table->index(['sender_id', 'status']);
            $table->index(['receiver_id', 'status']);
            $table->index(['category_id']);
            $table->index(['expires_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_files');
    }
};
