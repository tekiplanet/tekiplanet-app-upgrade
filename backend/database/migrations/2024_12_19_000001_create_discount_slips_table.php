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
        Schema::create('discount_slips', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('user_id');
            $table->uuid('user_conversion_task_id')->nullable(); // Can be null for non-task discounts
            $table->string('service_name');
            $table->integer('discount_percent');
            $table->string('discount_code')->unique(); // Unique discount code
            $table->timestamp('expires_at');
            $table->boolean('is_used')->default(false);
            $table->timestamp('used_at')->nullable();
            $table->text('terms_conditions')->nullable();
            $table->json('metadata')->nullable(); // For any additional data
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('user_conversion_task_id')->references('id')->on('user_conversion_tasks')->onDelete('set null');
            
            // Index for quick lookups
            $table->index(['discount_code']);
            $table->index(['user_id', 'is_used']);
            $table->index(['expires_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('discount_slips');
    }
};
