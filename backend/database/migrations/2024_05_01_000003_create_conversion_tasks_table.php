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
        Schema::create('conversion_tasks', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('title');
            $table->text('description')->nullable();
            $table->uuid('task_type_id');
            $table->integer('min_points');
            $table->integer('max_points');
            $table->uuid('reward_type_id');
            // New fields for dynamic rewards/tasks
            $table->uuid('product_id')->nullable(); // for product-based tasks/rewards
            $table->uuid('coupon_id')->nullable(); // for coupon rewards
            $table->uuid('course_id')->nullable(); // for course access rewards or course-based tasks
            $table->decimal('cash_amount', 12, 2)->nullable(); // for cash rewards
            $table->integer('discount_percent')->nullable(); // for discount code rewards
            $table->string('service_name')->nullable(); // for discount code rewards
            $table->timestamps();

            $table->foreign('task_type_id')->references('id')->on('conversion_task_types')->onDelete('cascade');
            $table->foreign('reward_type_id')->references('id')->on('conversion_reward_types')->onDelete('cascade');
            $table->foreign('product_id')->references('id')->on('products')->onDelete('set null');
            $table->foreign('coupon_id')->references('id')->on('coupons')->onDelete('set null');
            $table->foreign('course_id')->references('id')->on('courses')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('conversion_tasks');
    }
};
