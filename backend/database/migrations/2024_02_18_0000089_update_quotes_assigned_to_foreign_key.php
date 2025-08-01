<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateQuotesAssignedToForeignKey extends Migration
{
    public function up()
    {
        Schema::table('quotes', function (Blueprint $table) {
            // Drop the existing foreign key
            $table->dropForeign(['assigned_to']);
            
            // Add new foreign key referencing admins table
            $table->foreign('assigned_to')
                  ->references('id')
                  ->on('admins')
                  ->onDelete('set null');
        });
    }

    public function down()
    {
        Schema::table('quotes', function (Blueprint $table) {
            // Drop the new foreign key
            $table->dropForeign(['assigned_to']);
            
            // Restore original foreign key to users table
            $table->foreign('assigned_to')
                  ->references('id')
                  ->on('users')
                  ->onDelete('set null');
        });
    }
} 