<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Faker\Factory as Faker;

class BookingSeeder extends Seeder
{
    public function run()
    {
        $faker = Faker::create();

        $bookings = [];

        for ($i = 0; $i < 500; $i++) {
            $bookings[] = [
                'booking_id' => $faker->unique()->numberBetween(100000, 999999),
                'customer_id' => $faker->numberBetween(1, 1000),  // Assuming customer IDs range from 1 to 1000
                'screening_id' => $faker->numberBetween(1, 100),  // Assuming screening IDs range from 1 to 100
                'set_number' => $faker->word(),
                'status' => $faker->randomElement(['pending', 'confirmed', 'cancelled']),
                'created_at' => $faker->dateTimeThisYear(),
                'updated_at' => $faker->dateTimeThisYear(),
            ];
        }

        // Insert data into the bookings table
        DB::table('booking')->insert($bookings);
    }
}
