<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Faker\Factory as Faker;

class CinemaSeeder extends Seeder
{
    public function run()
    {
        $faker = Faker::create();

        // Reset unique state
        $faker->unique(true);

        $cinemas = [];

        $mall_ids = DB::table('malls')->pluck('mall_id')->toArray();
        $manager_ids = DB::table('managers')->pluck('manager_id')->toArray();

        for ($i = 0; $i < 1000; $i++) {
            $cinemas[] = [
                'cinema_id' => $faker->unique()->numberBetween(1, 1000),
                'mall_id' => $faker->randomElement($mall_ids),
                'manager_id' => $faker->randomElement($manager_ids),
                'name' => $faker->company() . ' Cinema',
                'active' => $faker->numberBetween(0, 1),  // remove unique here
                'created_at' => $faker->dateTimeThisYear(),
                'updated_at' => $faker->dateTimeThisYear(),
            ];
        }

        DB::table('cinemas')->insert($cinemas);
    }
}
