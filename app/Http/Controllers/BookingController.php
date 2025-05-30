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

    public function dataTables(Request $request) {
        $filter = $request->input('filter', '');
        $status_filter = $request->input('status_filter', '');
        
        $query = 'SELECT 
                b.*,
                CONCAT(c.first_name, " ", c.last_name) as customer_name,
                cin.name as cinema_name,
                cin.cinema_id,
                m.title as movie_title,
                m.movie_id,
                s.screening_time,
                s.screening_id
            FROM booking b
            JOIN customer c ON b.customer_id = c.customer_id AND c.active = 1
            JOIN screenings s ON b.screening_id = s.screening_id AND s.active = 1
            JOIN cinemas cin ON s.cinema_id = cin.cinema_id AND cin.active = 1
            JOIN movies m ON s.movie_id = m.movie_id AND m.active = 1
            WHERE 1=1';

        // Apply active/inactive filter
        if ($filter === '1') {
            $query .= ' AND b.active = 1';
        } elseif ($filter === '0') {
            $query .= ' AND b.active = 0';
        }

        // Apply status filter
        if ($status_filter) {
            $query .= ' AND LOWER(b.status) = LOWER(?)';
        }

        $query .= ' ORDER BY b.booking_id DESC';

        // Execute query with or without status parameter
        $bookings = $status_filter ? 
            DB::select($query, [$status_filter]) : 
            DB::select($query);

        return DataTables::of($bookings)->make(true);
    }

    public function store(Request $request)
    {
        try {
            // Basic request validation
            $request->validate([
                'customer_full_name' => 'required|string|max:255',
                'cinema_select' => 'required|numeric',
                'movie_select' => 'required|numeric',
                'time' => 'required|numeric', // Changed to numeric as we're receiving screening_id
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

            // 2. Verify screening exists and is active
            $screening = DB::select("
                SELECT screening_id 
                FROM screenings 
                WHERE screening_id = ? 
                AND active = 1", 
                [$request->time] // time field now contains screening_id
            );

            if (empty($screening)) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Invalid or inactive screening selected.'
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

            // 4. Save booking
            DB::insert("
                INSERT INTO booking (
                    customer_id, 
                    screening_id, 
                    set_number, 
                    status, 
                    active,
                    created_at, 
                    updated_at
                ) VALUES (?, ?, ?, ?, ?, NOW(), NOW())", 
                [
                    $customer[0]->customer_id,
                    $screening[0]->screening_id,
                    $request->seat_number,
                    $request->status,
                    1
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
                'cinema_select' => 'required|numeric',
                'movie_select' => 'required|numeric',
                'time' => 'required|numeric', // This is now screening_id
                'seat_number' => 'required|string|max:10',
                'status' => 'required|in:confirmed,pending,cancelled'
            ]);

            return DB::transaction(function () use ($request, $id) {
                // Check if booking exists
                $booking = DB::select('SELECT * FROM booking WHERE booking_id = ?', [$id]);
                if (empty($booking)) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Booking not found'
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
                        $request->cinema_select, // This is cinema_id
                        $request->movie_select  // This is movie_id
                    ]
                );

                if (empty($screening)) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Invalid screening selection'
                    ], 404);
                }

                // Check if seat is available (excluding current booking)
                $existingBooking = DB::select('
                    SELECT booking_id 
                    FROM booking 
                    WHERE screening_id = ? 
                    AND set_number = ? 
                    AND booking_id != ? 
                    AND status != ?', 
                    [
                        $screening[0]->screening_id,
                        $request->seat_number,
                        $id,
                        'cancelled'
                    ]
                );

                if (!empty($existingBooking)) {
                    return response()->json([
                        'success' => false,
                        'message' => 'This seat is already booked'
                    ], 400);
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
                        $screening[0]->screening_id,
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
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update booking: ' . $e->getMessage()
            ], 500);
        }
    }

    public function restore($id)
    {
        try {
            // Check if booking exists
            $booking = DB::select('SELECT * FROM booking WHERE booking_id = ?', [$id]);
            
            if (empty($booking)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Booking not found'
                ], 404);
            }

            // Update the active status to 1
            DB::update('UPDATE booking SET active = 1 WHERE booking_id = ?', [$id]);

            return response()->json([
                'success' => true,
                'message' => 'Booking has been restored successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to restore booking: ' . $e->getMessage()
            ], 500);
        }
    }
}
