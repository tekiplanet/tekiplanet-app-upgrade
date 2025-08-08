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
            $table->integer('enrollment_target')->default(1)->after('share_target');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('conversion_tasks', function (Blueprint $table) {
            $table->dropColumn('enrollment_target');
        });
    }
};
