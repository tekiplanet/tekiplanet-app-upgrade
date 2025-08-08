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
        Schema::create('course_share_enrollments', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('user_course_share_id');
            $table->uuid('enrollment_id');
            $table->uuid('enrolled_user_id'); // who made the enrollment
            $table->timestamp('enrolled_at');
            $table->decimal('enrollment_amount', 12, 2); // total enrollment amount
            $table->enum('status', ['pending', 'completed', 'cancelled'])->default('pending');
            $table->timestamps();

            $table->foreign('user_course_share_id')->references('id')->on('user_course_shares')->onDelete('cascade');
            $table->foreign('enrollment_id')->references('id')->on('enrollments')->onDelete('cascade');
            $table->foreign('enrolled_user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('course_share_enrollments');
    }
};
