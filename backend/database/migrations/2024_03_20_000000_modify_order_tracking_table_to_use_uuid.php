<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('order_tracking', function (Blueprint $table) {
            // Drop the auto-incrementing id column
            $table->dropColumn('id');
        });

        Schema::table('order_tracking', function (Blueprint $table) {
            // Add UUID as primary key
            $table->uuid('id')->first()->primary();
        });
    }

    public function down()
    {
        Schema::table('order_tracking', function (Blueprint $table) {
            // Drop the UUID column
            $table->dropColumn('id');
        });

        Schema::table('order_tracking', function (Blueprint $table) {
            // Restore auto-incrementing id
            $table->id()->first();
        });
    }
}; 