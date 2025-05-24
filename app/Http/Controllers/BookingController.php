<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\DataTables;

class BookingController extends Controller
{
    public function list () {
        return view ('bookings.list');
    }
     public function dataTables () {
        
     $bookings = DB::select('SELECT * FROM booking');
       return DataTables::of($bookings)->make(true);
    }


    public function store(Request $request)
    {
        try {
            // Validate the request
            $request->validate([
                'customer_full_name' => 'required|string|max:255',
                'screening_id' => 'required|integer',
                'seat_number' => 'required|string|max:10',
                'status' => 'required|in:confirmed,cancelled'
            ]);

            // Split the full name into first and last name
            $fullName = trim($request->customer_full_name);
            $nameParts = preg_split('/\s+/', $fullName);

            if (count($nameParts) < 2) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Please provide both first and last name for the customer.',
                ], 422);
            }

            $firstName = $nameParts[0];
            $lastName = $nameParts[1];

            // Check if customer exists
            $customer = DB::select('
                SELECT customer_id 
                FROM customer 
                WHERE first_name = ? 
                AND last_name = ? 
                LIMIT 1', 
                [$firstName, $lastName]
            );

            if (empty($customer)) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Customer not found. Please register the customer first.'
                ], 404);
            }

            $customerId = $customer[0]->customer_id;

            // Check if seat is already booked
            $existingBooking = DB::select("
                SELECT booking_id 
                FROM booking 
                WHERE screening_id = ? 
                AND set_number = ? 
                AND status = 'confirmed'
                AND active = 1", 
                [
                    $request->screening_id,
                    $request->seat_number
                ]
            );

            if (!empty($existingBooking)) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'This seat is already booked for this screening.'
                ], 422);
            }

            // Insert the booking
            DB::insert("
                INSERT INTO booking (
                    customer_id,
                    screening_id,
                    set_number,
                    status,
                    active,
                    created_at,
                    updated_at
                ) VALUES (?, ?, ?, ?, 1, NOW(), NOW())
            ", [
                $customerId,
                $request->screening_id,
                $request->seat_number,
                $request->status
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'Booking created successfully'
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to create booking: ' . $e->getMessage()
            ], 500);
        }
    }
}
