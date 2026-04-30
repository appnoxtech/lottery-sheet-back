<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class LotteryTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $types = ['Daily', 'Weekly', 'Special', 'Mega Jackpot'];
        foreach ($types as $type) {
            \App\Models\LotteryType::updateOrCreate(['name' => $type]);
        }
    }
}
