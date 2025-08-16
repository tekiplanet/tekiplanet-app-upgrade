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
        Schema::create('file_system_settings', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('setting_key', 100)->unique();
            $table->text('setting_value');
            $table->string('setting_type', 50)->default('string'); // string, integer, boolean, json
            $table->text('description')->nullable();
            $table->boolean('is_editable')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('file_system_settings');
    }
};
