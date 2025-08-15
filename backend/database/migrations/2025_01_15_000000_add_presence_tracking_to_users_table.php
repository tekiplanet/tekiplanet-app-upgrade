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
        Schema::table('users', function (Blueprint $table) {
            $table->enum('online_status', ['online', 'away', 'offline'])->default('offline')->after('profile_visibility');
            $table->timestamp('last_seen_at')->nullable()->after('online_status');
            $table->timestamp('last_activity_at')->nullable()->after('last_seen_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['online_status', 'last_seen_at', 'last_activity_at']);
        });
    }
};
