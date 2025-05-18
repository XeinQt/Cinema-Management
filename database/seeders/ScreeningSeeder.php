<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Faker\Factory as Faker;

class ScreeningSeeder extends Seeder
{
    public function run()
    {
        $faker = Faker::create();

        // Assuming cinema_id and movie_id already exist in their respective tables
        $cinema_ids = DB::table('cinemas')->pluck('cinema_id')->toArray();
        $movie_ids = DB::table('movies')->pluck('movie_id')->toArray();

        $screenings = [];

        for ($i = 0; $i < 1000; $i++) {
            $screenings[] = [
                'screening_id' => $faker->unique()->numberBetween(1, 1000),
                'cinema_id' => $faker->randomElement($cinema_ids),
                'movie_id' => $faker->randomElement($movie_ids),
                'screening_time' => $faker->dateTimeBetween('now', '+1 month')->format('Y-m-d H:i:s'),
                 'active' => $faker->numberBetween(0,1),
                'created_at' => $faker->dateTimeThisYear(),
                'updated_at' => $faker->dateTimeThisYear(),
            ];
        }

        DB::table('screening')->insert($screenings);
    }
}
