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

        $cinema_ids = DB::table('cinemas')->pluck('cinema_id')->toArray();
        $movie_ids = DB::table('movies')->pluck('movie_id')->toArray();

        $screenings = [];

        for ($i = 0; $i < 1000; $i++) {
            $screenings[] = [
                // 'screening_id' => $faker->unique()->numberBetween(1, 1000), // usually omit if auto-increment
                'cinema_id' => $faker->randomElement($cinema_ids),
                'movie_id' => $faker->randomElement($movie_ids),
                'screening_time' => $faker->dateTimeBetween('now', '+1 month')->format('Y-m-d H:i:s'),
                'active' => $faker->numberBetween(0, 1),
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        DB::table('screenings')->insert($screenings);
    }
}
