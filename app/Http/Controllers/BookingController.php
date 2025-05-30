<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\DataTables;
use Carbon\Carbon;

class BookingController extends Controller
{
    public function list() {
        return view('bookings.list');
    }

    public function dataTables() {
        $bookings = DB::select('SELECT * FROM booking');
        return DataTables::of($bookings)->make(true);
    }

    public function store(Request $request)
    {
        try {
            // Basic request validation
            $request->validate([
                'customer_full_name' => 'required|string|max:255',
                'cinema_name' => 'required|numeric',
                'movie_title' => 'required|numeric',
                'time' => 'required|date',
                'seat_number' => 'required|string|max:10',
                'status' => 'required|in:confirmed,pending,cancelled'
            ]);

            // 1. Get customer ID from full name
            $nameParts = explode(' ', trim($request->customer_full_name));
            if (count($nameParts) < 2) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Please provide both first and last name'
                ], 400);
            }

            $firstName = $nameParts[0];
            $lastName = end($nameParts);

            $customer = DB::select("
                SELECT customer_id 
                FROM customer
                WHERE LOWER(first_name) = LOWER(?) 
                AND LOWER(last_name) = LOWER(?)", 
                [$firstName, $lastName]
            );

            if (empty($customer)) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Customer not found. Please register the customer first.'
                ], 404);
            }

            // 2. Get screening ID using cinema_id and movie_id
            $screening = DB::select("
                SELECT screening_id 
                FROM screenings 
                WHERE cinema_id = ? 
                AND movie_id = ? 
                AND screening_time = ?", 
                [
                    $request->cinema_name,
                    $request->movie_title,
                    $request->time
                ]
            );

            if (empty($screening)) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'No screening found for the selected time.'
                ], 404);
            }

            // 3. Check if seat is available
            $existingBooking = DB::select("
                SELECT booking_id 
                FROM booking
                WHERE screening_id = ? 
                AND set_number = ? 
                AND status != 'cancelled'", 
                [
                    $screening[0]->screening_id,
                    $request->seat_number
                ]
            );

            if (!empty($existingBooking)) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'This seat is already booked.'
                ], 400);
            }

            // 4. Save booking with only IDs
            DB::insert("
                INSERT INTO booking (
                    customer_id, 
                    screening_id, 
                    set_number, 
                    status, 
                    created_at, 
                    updated_at
                ) VALUES (?, ?, ?, ?, NOW(), NOW())", 
                [
                    $customer[0]->customer_id,
                    $screening[0]->screening_id,
                    $request->seat_number,
                    $request->status
                ]
            );

            return response()->json([
                'success' => true,
                'message' => 'Booking created successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to create booking: ' . $e->getMessage()
            ], 500);
        }
    }

    public function updateStatus($id)
    {
        try {
            $booking = DB::select('SELECT * FROM booking WHERE booking_id = ?', [$id]);
            
            if (empty($booking)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Booking not found'
                ], 404);
            }

            // Update the active status to 0
            DB::update('UPDATE booking SET active = 0 WHERE booking_id = ?', [$id]);

            return response()->json([
                'success' => true,
                'message' => 'Booking has been deactivated successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to deactivate booking: ' . $e->getMessage()
            ], 500);
        }
    }
}
