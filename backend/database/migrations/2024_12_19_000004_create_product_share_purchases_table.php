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
        Schema::create('product_share_purchases', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('user_product_share_id');
            $table->uuid('order_id');
            $table->uuid('purchaser_user_id');
            $table->timestamp('purchased_at')->nullable();
            $table->decimal('order_amount', 10, 2);
            $table->string('status')->default('pending'); // pending, completed, cancelled
            $table->timestamps();

            $table->foreign('user_product_share_id')->references('id')->on('user_product_shares')->onDelete('cascade');
            $table->foreign('order_id')->references('id')->on('orders')->onDelete('cascade');
            $table->foreign('purchaser_user_id')->references('id')->on('users')->onDelete('cascade');
            
            $table->index(['user_product_share_id']);
            $table->index(['order_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_share_purchases');
    }
};
