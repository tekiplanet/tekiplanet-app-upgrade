<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        // Update the enum to include 'owner' and 'system'
        DB::statement("ALTER TABLE grit_messages MODIFY COLUMN sender_type ENUM('admin', 'professional', 'owner', 'system') NOT NULL");
    }

    public function down()
    {
        // Revert back to original enum values
        DB::statement("ALTER TABLE grit_messages MODIFY COLUMN sender_type ENUM('admin', 'professional') NOT NULL");
    }
};
