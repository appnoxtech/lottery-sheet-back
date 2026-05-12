<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddSortOrderToLotteryTypesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('lottery_types', function (Blueprint $table) {
            $table->integer('sort_order')->default(0)->after('is_active');
        });
        
        // Set initial sort_order values for existing records
        \DB::table('lottery_types')->orderBy('id')->get()->each(function ($type, $index) {
            \DB::table('lottery_types')->where('id', $type->id)->update(['sort_order' => $index + 1]);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('lottery_types', function (Blueprint $table) {
            $table->dropColumn('sort_order');
        });
    }
}
