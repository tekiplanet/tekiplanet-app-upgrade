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
        Schema::create('grit_negotiations', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('grit_id');
            $table->uuid('proposed_by_user_id');
            $table->decimal('proposed_owner_budget', 10, 2);
            $table->string('proposed_owner_currency', 3);
            $table->decimal('proposed_professional_budget', 10, 2);
            $table->string('proposed_professional_currency', 3);
            $table->date('proposed_deadline')->nullable();
            $table->text('proposed_requirements')->nullable();
            $table->enum('status', ['pending', 'accepted', 'rejected', 'withdrawn'])->default('pending');
            $table->text('response_message')->nullable();
            $table->timestamp('responded_at')->nullable();
            $table->timestamps();

            $table->foreign('grit_id')->references('id')->on('grits')->onDelete('cascade');
            $table->foreign('proposed_by_user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('grit_negotiations');
    }
};
