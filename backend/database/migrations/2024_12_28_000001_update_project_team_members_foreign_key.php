<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('project_team_members', function (Blueprint $table) {
            // Drop the old foreign key and column
            // $table->dropForeign(['user_id']);
            // $table->dropColumn('user_id');
            
            // Add the new column and foreign key
            $table->foreignUuid('professional_id')
                  ->after('project_id')
                  ->constrained('professionals')
                  ->cascadeOnDelete();
        });
    }

    public function down()
    {
        Schema::table('project_team_members', function (Blueprint $table) {
            $table->dropForeign(['professional_id']);
            $table->dropColumn('professional_id');
            
            $table->foreignUuid('user_id')
                  ->after('project_id')
                  ->constrained()
                  ->cascadeOnDelete();
        });
    }
}; 