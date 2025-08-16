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
        Schema::create('file_categories', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name', 100)->unique();
            $table->text('description')->nullable();
            $table->json('allowed_extensions');
            $table->bigInteger('max_file_size'); // in bytes
            $table->enum('resource_type', ['image', 'video', 'raw'])->default('raw');
            $table->boolean('is_active')->default(true);
            $table->boolean('requires_optimization')->default(false);
            $table->json('cloudinary_options')->nullable(); // Additional Cloudinary options
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('file_categories');
    }
};
