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
        Schema::table('grits', function (Blueprint $table) {
            // Business Owner columns
            $table->uuid('created_by_user_id')->nullable()->after('id');
            $table->enum('admin_approval_status', ['pending', 'approved', 'rejected'])->default('pending')->after('status');

            // Multicurrency Budget columns
            $table->decimal('owner_budget', 10, 2)->after('budget');
            $table->string('owner_currency', 3)->after('owner_budget');
            $table->decimal('professional_budget', 10, 2)->after('owner_currency');
            $table->string('professional_currency', 3)->after('professional_budget');

            // Workflow Management columns
            $table->enum('negotiation_status', ['none', 'pending', 'accepted', 'rejected'])->default('none');
            $table->timestamp('terms_modified_at')->nullable();
            $table->timestamp('project_started_at')->nullable();
            $table->timestamp('completion_requested_at')->nullable();

            // Feedback and Dispute columns
            $table->enum('owner_satisfaction', ['pending', 'satisfied', 'unsatisfied'])->default('pending');
            $table->tinyInteger('owner_rating')->nullable();
            $table->enum('dispute_status', ['none', 'raised_by_owner', 'raised_by_professional', 'resolved'])->default('none');

            // Project Integration column
            $table->uuid('project_id')->nullable();

            // Foreign Keys
            $table->foreign('created_by_user_id')->references('id')->on('users')->onDelete('set null');
            $table->foreign('project_id')->references('id')->on('projects')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('grits', function (Blueprint $table) {
            $table->dropForeign(['created_by_user_id']);
            $table->dropForeign(['project_id']);

            $table->dropColumn([
                'created_by_user_id',
                'admin_approval_status',
                'owner_budget',
                'owner_currency',
                'professional_budget',
                'professional_currency',
                'negotiation_status',
                'terms_modified_at',
                'project_started_at',
                'completion_requested_at',
                'owner_satisfaction',
                'owner_rating',
                'dispute_status',
                'project_id',
            ]);
        });
    }
};
