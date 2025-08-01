<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    // public function up(): void
    // {
    //     Schema::table('courses', function (Blueprint $table) {
    //         // Add category_id column after the category column
    //         $table->uuid('category_id')->nullable()->after('category');
            
    //         // Add foreign key constraint
    //         $table->foreign('category_id')
    //               ->references('id')
    //               ->on('course_categories')
    //               ->onDelete('set null');
    //     });
    // }

    // /**
    //  * Reverse the migrations.
    //  */
    // public function down(): void
    // {
    //     Schema::table('courses', function (Blueprint $table) {
    //         // Remove foreign key first
    //         $table->dropForeign(['category_id']);
            
    //         // Then remove the column
    //         $table->dropColumn('category_id');
    //     });
    // }
}; 