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
            $table->timestamps();

            $table->foreign('task_type_id')->references('id')->on('conversion_task_types')->onDelete('cascade');
            $table->foreign('reward_type_id')->references('id')->on('conversion_reward_types')->onDelete('cascade');
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
