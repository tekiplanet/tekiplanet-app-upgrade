<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('grit_disputes', function (Blueprint $table) {
            if (!Schema::hasColumn('grit_disputes', 'resolution_details')) {
                $table->text('resolution_details')->nullable()->after('status');
            }
            if (!Schema::hasColumn('grit_disputes', 'winner_id')) {
                $table->foreignUuid('winner_id')->nullable()->after('resolution_details')->constrained('users')->onDelete('set null');
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('grit_disputes', function (Blueprint $table) {
            if (Schema::hasColumn('grit_disputes', 'winner_id')) {
                $table->dropForeign(['winner_id']);
                $table->dropColumn('winner_id');
            }
            if (Schema::hasColumn('grit_disputes', 'resolution_details')) {
                $table->dropColumn('resolution_details');
            }
        });
    }
};
