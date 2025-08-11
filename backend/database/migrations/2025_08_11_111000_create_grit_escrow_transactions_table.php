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
        Schema::create('grit_escrow_transactions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('grit_id');
            $table->uuid('user_id');
            $table->enum('transaction_type', ['freeze', 'release', 'refund']);
            $table->decimal('owner_amount', 10, 2);
            $table->string('owner_currency', 3);
            $table->decimal('professional_amount', 10, 2);
            $table->string('professional_currency', 3);
            $table->decimal('percentage', 5, 2)->nullable();
            $table->text('description')->nullable();
            $table->enum('status', ['pending', 'completed', 'failed'])->default('pending');
            $table->timestamps();

            $table->foreign('grit_id')->references('id')->on('grits')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('grit_escrow_transactions');
    }
};
