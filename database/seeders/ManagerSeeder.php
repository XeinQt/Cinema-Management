<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Faker\Factory as Faker;

class ManagerSeeder extends Seeder
{
    public function run()
    {
        $faker = Faker::create();

        $managers = [];

        for ($i = 0; $i < 1000; $i++) {
            $managers[] = [
                'manager_id' => $faker->unique()->numberBetween(1, 1000),
                'first_name' => $faker->firstName(),
                'last_name' => $faker->lastName(),
                'email' => $faker->unique()->safeEmail(),
                'phonenumber' => $faker->phoneNumber(),
                'created_at' => $faker->dateTimeThisYear(),
                'updated_at' => $faker->dateTimeThisYear(),
            ];
        }

        DB::table('managers')->insert($managers);
    }
}
