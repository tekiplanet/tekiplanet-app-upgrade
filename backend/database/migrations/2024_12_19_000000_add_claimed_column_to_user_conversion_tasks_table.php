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
        Schema::table('user_conversion_tasks', function (Blueprint $table) {
            $table->boolean('claimed')->default(false)->after('status');
            $table->timestamp('claimed_at')->nullable()->after('claimed');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('user_conversion_tasks', function (Blueprint $table) {
            $table->dropColumn(['claimed', 'claimed_at']);
        });
    }
};
