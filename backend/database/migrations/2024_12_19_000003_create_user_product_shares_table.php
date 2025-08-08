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
        Schema::create('user_product_shares', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('user_id');
            $table->uuid('user_conversion_task_id');
            $table->uuid('product_id');
            $table->string('share_link', 500);
            $table->timestamp('shared_at')->nullable();
            $table->integer('purchase_count')->default(0);
            $table->string('status')->default('active'); // active, completed, expired
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('user_conversion_task_id')->references('id')->on('user_conversion_tasks')->onDelete('cascade');
            $table->foreign('product_id')->references('id')->on('products')->onDelete('cascade');
            
            $table->index(['share_link']);
            $table->index(['user_id', 'user_conversion_task_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_product_shares');
    }
};
