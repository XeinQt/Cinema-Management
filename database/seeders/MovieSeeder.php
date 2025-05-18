<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Faker\Factory as Faker;

class MovieSeeder extends Seeder
{
    public function run()
    {
        $faker = Faker::create();

        $genres = ['Action', 'Drama', 'Comedy', 'Horror', 'Sci-Fi', 'Romance', 'Thriller', 'Adventure', 'Animation', 'Documentary'];

        $movies = [];

        for ($i = 0; $i < 1000; $i++) {
            $movies[] = [
                'movie_id' => $faker->unique()->numberBetween(1, 1000),
                'title' => $faker->sentence(3),
                'genre' => $faker->randomElement($genres),
                'duration' => $faker->numberBetween(80, 180) . ' minutes',
                'description' => $faker->sentence(10),
                'rating' => $faker->randomElement(['G', 'PG', 'PG-13', 'R', 'NC-17']),
                 'active' => $faker->numberBetween(0,1),
                'created_at' => $faker->dateTimeThisYear(),
                'updated_at' => $faker->dateTimeThisYear(),
            ];
        }

        DB::table('movies')->insert($movies);
    }
}
