<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('lesson_progress', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('user_id');
            $table->uuid('lesson_id');
            $table->uuid('course_id');
            $table->timestamp('completed_at');
            $table->timestamps();
            
            $table->foreign('user_id')
                  ->references('id')
                  ->on('users')
                  ->onDelete('cascade');
            
            $table->foreign('lesson_id')
                  ->references('id')
                  ->on('course_lessons')
                  ->onDelete('cascade');
            
            $table->foreign('course_id')
                  ->references('id')
                  ->on('courses')
                  ->onDelete('cascade');
            
            // Ensure a user can only complete a lesson once
            $table->unique(['user_id', 'lesson_id']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('lesson_progress');
    }
}; 