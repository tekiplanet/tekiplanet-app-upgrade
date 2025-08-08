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
        Schema::create('course_share_visits', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('user_course_share_id');
            $table->string('visitor_ip')->nullable(); // visitor's IP address
            $table->string('user_agent')->nullable(); // visitor's browser/device info
            $table->string('referrer')->nullable(); // where visitor came from
            $table->timestamp('visited_at'); // when the visit occurred
            $table->timestamps();

            $table->foreign('user_course_share_id')->references('id')->on('user_course_shares')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('course_share_visits');
    }
};
