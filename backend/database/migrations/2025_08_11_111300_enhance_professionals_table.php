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
        Schema::table('professionals', function (Blueprint $table) {
            $table->decimal('completion_rate', 5, 2)->default(0.00)->after('status');
            $table->decimal('average_rating', 3, 2)->default(0.00)->after('completion_rate');
            $table->integer('total_projects_completed')->default(0)->after('average_rating');
            $table->json('qualifications')->nullable()->after('total_projects_completed');
            $table->json('portfolio_items')->nullable()->after('qualifications');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('professionals', function (Blueprint $table) {
            $table->dropColumn([
                'completion_rate',
                'average_rating',
                'total_projects_completed',
                'qualifications',
                'portfolio_items',
            ]);
        });
    }
};
