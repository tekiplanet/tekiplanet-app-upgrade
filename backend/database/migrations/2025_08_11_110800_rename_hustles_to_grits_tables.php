<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::rename('hustles', 'grits');
        Schema::rename('hustle_applications', 'grit_applications');
        Schema::rename('hustle_messages', 'grit_messages');
        Schema::rename('hustle_payments', 'grit_payments');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::rename('grits', 'hustles');
        Schema::rename('grit_applications', 'hustle_applications');
        Schema::rename('grit_messages', 'hustle_messages');
        Schema::rename('grit_payments', 'hustle_payments');
    }
};
