<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddLotterySelectionsAndNumberTypesToLotteryRequests extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('lottery_requests', function (Blueprint $table) {
            $table->json('lottery_selections')->nullable()->after('lottery_numbers');
            $table->json('number_types')->nullable()->after('lottery_selections');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('lottery_requests', function (Blueprint $table) {
            $table->dropColumn('lottery_selections');
            $table->dropColumn('number_types');
        });
    }
}
