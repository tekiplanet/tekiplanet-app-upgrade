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
        Schema::create('conversion_task_rewards', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('conversion_task_id');
            $table->decimal('amount', 12, 2)->nullable(); // for cash or discount
            $table->string('coupon_code')->nullable(); // for coupon
            $table->unsignedBigInteger('course_id')->nullable(); // for course access
            $table->integer('discount_percent')->nullable(); // for discount
            $table->text('details')->nullable(); // any extra info
            $table->timestamps();

            $table->foreign('conversion_task_id')->references('id')->on('conversion_tasks')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('conversion_task_rewards');
    }
};
