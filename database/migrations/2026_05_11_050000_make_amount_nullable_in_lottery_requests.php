<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class MakeAmountNullableInLotteryRequests extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::statement('ALTER TABLE lottery_requests MODIFY amount DECIMAL(10, 2) NULL');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::statement('ALTER TABLE lottery_requests MODIFY amount DECIMAL(10, 2) NOT NULL');
    }
}
