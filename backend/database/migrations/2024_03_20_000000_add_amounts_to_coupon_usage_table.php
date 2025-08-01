<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('coupon_usage', function (Blueprint $table) {
            $table->decimal('order_amount', 10, 2)->after('order_id');
            $table->decimal('discount_amount', 10, 2)->after('order_amount');
        });
    }

    public function down()
    {
        Schema::table('coupon_usage', function (Blueprint $table) {
            $table->dropColumn(['order_amount', 'discount_amount']);
        });
    }
}; 