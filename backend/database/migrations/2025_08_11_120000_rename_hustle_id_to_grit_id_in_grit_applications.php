<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // MariaDB doesn't support RENAME COLUMN, so we use raw SQL
        DB::statement('ALTER TABLE grit_applications CHANGE hustle_id grit_id CHAR(36)');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Reverse the change
        DB::statement('ALTER TABLE grit_applications CHANGE grit_id hustle_id CHAR(36)');
    }
};
