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
        $customer_ids = DB::table('customer')->pluck('customer_id')->toArray();
        $screening_ids = DB::table('screenings')->pluck('screening_id')->toArray();

        for ($i = 0; $i < 500; $i++) {
            $bookings[] = [
                'booking_id' => $faker->unique()->numberBetween(1, 1000),
                'customer_id' => $faker->randomElement($customer_ids), // Assuming customer IDs range from 1 to 1000
                'screening_id' => $faker->randomElement($screening_ids),  // Assuming screening IDs range from 1 to 100
                'set_number' => $faker->word(),
                'status' => $faker->randomElement(['confirmed', 'cancelled']),
                'active' => $faker->numberBetween(0,1),
                'created_at' => $faker->dateTimeThisYear(),
                'updated_at' => $faker->dateTimeThisYear(),
            ];
        }

        // Insert data into the bookings table
        DB::table('booking')->insert($bookings);
    }
}
