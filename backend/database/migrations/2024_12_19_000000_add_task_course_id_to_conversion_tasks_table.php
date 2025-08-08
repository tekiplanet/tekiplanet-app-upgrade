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
        Schema::table('conversion_tasks', function (Blueprint $table) {
            $table->uuid('task_course_id')->nullable()->after('course_id');
            $table->foreign('task_course_id')->references('id')->on('courses')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('conversion_tasks', function (Blueprint $table) {
            $table->dropForeign(['task_course_id']);
            $table->dropColumn('task_course_id');
        });
    }
};
