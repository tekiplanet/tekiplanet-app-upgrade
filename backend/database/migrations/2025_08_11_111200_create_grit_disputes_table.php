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
        Schema::create('grit_disputes', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('grit_id');
            $table->uuid('raised_by_user_id');
            $table->enum('dispute_type', ['payment', 'quality', 'deadline', 'scope', 'other']);
            $table->text('description');
            $table->enum('status', ['open', 'under_review', 'resolved', 'closed'])->default('open');
            $table->text('resolution_details')->nullable();
            $table->timestamp('resolved_at')->nullable();
            $table->timestamps();

            $table->foreign('grit_id')->references('id')->on('grits')->onDelete('cascade');
            $table->foreign('raised_by_user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('grit_disputes');
    }
};
