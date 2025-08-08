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
        Schema::create('user_course_shares', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('user_id');
            $table->uuid('user_conversion_task_id');
            $table->uuid('course_id');
            $table->string('share_link'); // unique tracking link
            $table->timestamp('shared_at');
            $table->integer('enrollment_count')->default(0); // tracks enrollments made through this share link
            $table->enum('status', ['active', 'completed', 'expired'])->default('active');
            $table->timestamp('expires_at')->nullable(); // 7-day expiration
            $table->integer('click_count')->default(0); // tracks total clicks on share link
            $table->string('visitor_session_id')->nullable(); // for session tracking
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('user_conversion_task_id')->references('id')->on('user_conversion_tasks')->onDelete('cascade');
            $table->foreign('course_id')->references('id')->on('courses')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_course_shares');
    }
};
