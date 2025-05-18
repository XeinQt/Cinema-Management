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

        // Reset Faker unique state
        $faker->unique(true);

        $malls = [];

        for ($i = 0; $i < 1000; $i++) {
            $malls[] = [
                'mall_id' => $faker->unique()->numberBetween(1, 1000),
                'name' => $faker->company(),
                'location' => $faker->city() . ', ' . $faker->state(),
                'description' => $faker->sentence(),
                'active' => $faker->numberBetween(0, 1), // no unique here
                'created_at' => $faker->dateTimeThisYear(),
                'updated_at' => $faker->dateTimeThisYear(),
            ];
        }

        DB::table('malls')->insert($malls);
    }
}
