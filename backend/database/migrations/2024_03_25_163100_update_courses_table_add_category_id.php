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
        Schema::table('courses', function (Blueprint $table) {
            // Keep the existing category column
            $table->string('category')->nullable()->change();
            // Add the new category_id column
            $table->uuid('category_id')->nullable()->after('category');
            
            // Add foreign key
            $table->foreign('category_id')
                  ->references('id')
                  ->on('course_categories')
                  ->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('courses', function (Blueprint $table) {
            $table->dropForeign(['category_id']);
            $table->dropColumn('category_id');
            $table->string('category')->change();
        });
    }
}; 