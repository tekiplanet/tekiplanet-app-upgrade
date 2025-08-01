<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('product_requests', function (Blueprint $table) {
            // Remove old column
            $table->dropColumn('expected_price_range');
            
            // Add new columns
            $table->decimal('min_price', 10, 2)->nullable();
            $table->decimal('max_price', 10, 2)->nullable();
            $table->date('deadline')->nullable();
        });
    }

    public function down()
    {
        Schema::table('product_requests', function (Blueprint $table) {
            // Restore old column
            $table->string('expected_price_range')->nullable();
            
            // Remove new columns
            $table->dropColumn(['min_price', 'max_price', 'deadline']);
        });
    }
}; 