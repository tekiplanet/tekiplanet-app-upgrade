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
        Schema::create('user_referrals', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('referrer_user_id');
            $table->uuid('referred_user_id');
            $table->uuid('user_conversion_task_id');
            $table->timestamp('registered_at')->nullable();
            $table->string('status')->default('pending'); // pending, completed, etc.
            $table->timestamps();

            $table->foreign('referrer_user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('referred_user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('user_conversion_task_id')->references('id')->on('user_conversion_tasks')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_referrals');
    }
};
