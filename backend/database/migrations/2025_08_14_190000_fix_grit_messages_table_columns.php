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
        // Use raw SQL to avoid Laravel's foreign key constraint issues
        \DB::statement('ALTER TABLE grit_messages CHANGE hustle_id grit_id CHAR(36) NOT NULL');
        
        // Add foreign key constraint using raw SQL
        \DB::statement('ALTER TABLE grit_messages ADD CONSTRAINT grit_messages_grit_id_foreign FOREIGN KEY (grit_id) REFERENCES grits(id) ON DELETE CASCADE');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Drop foreign key constraint using raw SQL
        \DB::statement('ALTER TABLE grit_messages DROP FOREIGN KEY grit_messages_grit_id_foreign');
        
        // Rename column back using raw SQL
        \DB::statement('ALTER TABLE grit_messages CHANGE grit_id hustle_id CHAR(36) NOT NULL');
        
        // Add back the old foreign key constraint
        \DB::statement('ALTER TABLE grit_messages ADD CONSTRAINT grit_messages_hustle_id_foreign FOREIGN KEY (hustle_id) REFERENCES hustles(id) ON DELETE CASCADE');
    }
};
