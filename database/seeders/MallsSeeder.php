<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Faker\Factory as Faker;

class MallsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $faker = Faker::create();

        $data = [];

        for ($i = 1; $i <= 1000; $i++) {
            $data[] = [
                'name' => 'Branch ' . $i,
                'location' => $faker->city,
                'description' => $faker->sentence(3),
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        // Insert in bulk
        DB::table('malls')->insert($data);
    }
}
