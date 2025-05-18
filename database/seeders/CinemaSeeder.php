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

        $cinemas = [];

        // Assuming you have at least 100 malls and 100 managers already seeded in your database
        $mall_ids = DB::table('malls')->pluck('mall_id')->toArray();
        $manager_ids = DB::table('managers')->pluck('manager_id')->toArray();

        for ($i = 0; $i < 1000; $i++) {
            $cinemas[] = [
                'cinema_id' => $faker->unique()->numberBetween(1, 1000),
                'mall_id' => $faker->randomElement($mall_ids),  // Random mall_id from the malls table
                'manager_id' => $faker->randomElement($manager_ids),  // Random manager_id from the managers table
                'name' => $faker->company() . ' Cinema',  // Random company name + " Cinema"
                'created_at' => $faker->dateTimeThisYear(),
                'updated_at' => $faker->dateTimeThisYear(),
            ];
        }

        // Insert data into the cinemas table
        DB::table('cinemas')->insert($cinemas);
    }
}
