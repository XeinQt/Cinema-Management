<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Faker\Factory as Faker;

class MallSeeder extends Seeder
{
    public function run()
    {
        $faker = Faker::create();

        $malls = [];

        for ($i = 0; $i < 1000; $i++) {
            $malls[] = [
                'mall_id' => $faker->unique()->numberBetween(1, 1000),
                'name' => $faker->company(),
                'location' => $faker->city() . ', ' . $faker->state(),
                'description' => $faker->sentence(),
                'created_at' => $faker->dateTimeThisYear(),
                'updated_at' => $faker->dateTimeThisYear(),
            ];
        }

        // Insert the generated malls data into the database
        DB::table('malls')->insert($malls);
    }
}
