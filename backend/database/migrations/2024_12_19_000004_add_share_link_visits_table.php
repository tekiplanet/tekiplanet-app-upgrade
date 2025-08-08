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
        Schema::create('share_link_visits', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('user_product_share_id');
            $table->string('visitor_ip', 45)->nullable();
            $table->string('user_agent', 500)->nullable();
            $table->string('referrer', 500)->nullable();
            $table->timestamp('visited_at');
            $table->timestamps();

            $table->foreign('user_product_share_id')->references('id')->on('user_product_shares')->onDelete('cascade');
            $table->index(['user_product_share_id', 'visited_at']);
            $table->index(['visitor_ip']);
        });

        // Add expiration and click tracking to user_product_shares
        Schema::table('user_product_shares', function (Blueprint $table) {
            $table->timestamp('expires_at')->nullable()->after('shared_at');
            $table->integer('click_count')->default(0)->after('purchase_count');
            $table->string('visitor_session_id', 100)->nullable()->after('click_count');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('share_link_visits');
        
        Schema::table('user_product_shares', function (Blueprint $table) {
            $table->dropColumn(['expires_at', 'click_count', 'visitor_session_id']);
        });
    }
};
