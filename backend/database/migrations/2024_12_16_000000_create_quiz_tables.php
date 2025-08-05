<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // Quiz Questions Table
        Schema::create('quiz_questions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('lesson_id');
            $table->text('question');
            $table->enum('question_type', ['multiple_choice', 'true_false', 'short_answer']);
            $table->integer('points')->default(1);
            $table->integer('order')->default(0);
            $table->timestamps();
            
            $table->foreign('lesson_id')
                  ->references('id')
                  ->on('course_lessons')
                  ->onDelete('cascade');
        });

        // Quiz Answers Table
        Schema::create('quiz_answers', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('question_id');
            $table->text('answer_text');
            $table->boolean('is_correct')->default(false);
            $table->integer('order')->default(0);
            $table->timestamps();
            
            $table->foreign('question_id')
                  ->references('id')
                  ->on('quiz_questions')
                  ->onDelete('cascade');
        });

        // Quiz Attempts Table
        Schema::create('quiz_attempts', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('user_id');
            $table->uuid('lesson_id');
            $table->integer('score')->default(0);
            $table->integer('total_points')->default(0);
            $table->decimal('percentage', 5, 2)->default(0);
            $table->boolean('passed')->default(false);
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
            
            $table->foreign('user_id')
                  ->references('id')
                  ->on('users')
                  ->onDelete('cascade');
            
            $table->foreign('lesson_id')
                  ->references('id')
                  ->on('course_lessons')
                  ->onDelete('cascade');
        });

        // Quiz Responses Table
        Schema::create('quiz_responses', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('attempt_id');
            $table->uuid('question_id');
            $table->text('user_answer');
            $table->boolean('is_correct')->default(false);
            $table->integer('points_earned')->default(0);
            $table->timestamps();
            
            $table->foreign('attempt_id')
                  ->references('id')
                  ->on('quiz_attempts')
                  ->onDelete('cascade');
            
            $table->foreign('question_id')
                  ->references('id')
                  ->on('quiz_questions')
                  ->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('quiz_responses');
        Schema::dropIfExists('quiz_attempts');
        Schema::dropIfExists('quiz_answers');
        Schema::dropIfExists('quiz_questions');
    }
}; 