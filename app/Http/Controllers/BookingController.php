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
        $bookings = DB::select('
            SELECT 
                b.*,
                CONCAT(c.first_name, " ", c.last_name) as customer_name,
                cin.name as cinema_name,
                cin.cinema_id,
                m.title as movie_title,
                m.movie_id,
                s.screening_time,
                s.screening_id
            FROM booking b
            JOIN customer c ON b.customer_id = c.customer_id
            JOIN screenings s ON b.screening_id = s.screening_id
            JOIN cinemas cin ON s.cinema_id = cin.cinema_id
            JOIN movies m ON s.movie_id = m.movie_id
            WHERE b.active = 1
            ORDER BY b.booking_id DESC
        ');
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

    public function update($id, Request $request)
    {
        try {
            // Basic request validation
            $request->validate([
                'customer_full_name' => 'required|string|max:255',
                'cinema_name' => 'required|numeric',
                'movie_title' => 'required|numeric',
                'time' => 'required|numeric', // This is now screening_id
                'seat_number' => 'required|string|max:10',
                'status' => 'required|in:confirmed,pending,cancelled'
            ]);

            return DB::transaction(function () use ($request, $id) {
                // Check if booking exists
                $booking = DB::select('SELECT * FROM booking WHERE booking_id = ? AND active = 1', [$id]);
                if (empty($booking)) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Booking not found or is inactive'
                    ], 404);
                }

                // Get customer ID from full name
                $nameParts = explode(' ', trim($request->customer_full_name));
                if (count($nameParts) < 2) {
                    return response()->json([
                        'success' => false,
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
                        'success' => false,
                        'message' => 'Customer not found. Please register the customer first.'
                    ], 404);
                }

                // Check if screening exists
                $screening = DB::select('
                    SELECT * FROM screenings 
                    WHERE screening_id = ? 
                    AND cinema_id = ? 
                    AND movie_id = ? 
                    AND active = 1', 
                    [
                        $request->time, // This is screening_id
                        $request->cinema_name, // This is cinema_id
                        $request->movie_title  // This is movie_id
                    ]
                );

                if (empty($screening)) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Invalid screening selection. Please check cinema, movie and time.'
                    ], 404);
                }

                // Check if seat is available (excluding current booking)
                $existingBooking = DB::select('
                    SELECT booking_id 
                    FROM booking 
                    WHERE screening_id = ? 
                    AND set_number = ? 
                    AND booking_id != ? 
                    AND status != ? 
                    AND active = 1', 
                    [
                        $request->time,
                        $request->seat_number,
                        $id,
                        'cancelled'
                    ]
                );

                if (!empty($existingBooking)) {
                    return response()->json([
                        'success' => false,
                        'message' => 'This seat is already booked.'
                    ], 409);
                }

                // Update the booking
                DB::update('
                    UPDATE booking 
                    SET customer_id = ?,
                        screening_id = ?,
                        set_number = ?,
                        status = ?,
                        updated_at = NOW()
                    WHERE booking_id = ?',
                    [
                        $customer[0]->customer_id,
                        $request->time, // screening_id
                        $request->seat_number,
                        $request->status,
                        $id
                    ]
                );

                return response()->json([
                    'success' => true,
                    'message' => 'Booking updated successfully'
                ]);
            });

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update booking: ' . $e->getMessage()
            ], 500);
        }
    }
}
