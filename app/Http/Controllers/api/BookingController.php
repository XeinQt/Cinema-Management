<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BookingController extends Controller
{  
     public function list()
    {
        $booking = DB::select('SELECT * FROM booking');
        return response()->json([
            'status' => 'success',
            'rows' => count($booking),
            'data' => $booking
        ], 200);
    }

    public function seacrhById($id)
    {
        $booking = DB::select('SELECT * FROM booking WHERE booking_id = ?', [$id]);
        return response()->json([
            'status' => 'success',
            'data' => $booking
        ], 200);
        
    }

    public function create(Request $request)
    {
        $booking = DB::insert('INSERT INTO booking (customer_id, screening_id, set_number, status, active) VALUES (?, ?, ?, ?, ?)', [
            $request->customer_id,
            $request->screening_id,
            $request->set_number,
            $request->status,
            $request->active,
        ]);
        
        return response()->json([
            'status' => 'success',
            'message' => 'Booking created successfully'
        ], 200);
    }

    public function update(Request $request, $id)
    {
        $booking = DB::update('UPDATE booking SET customer_id = ?, screening_id = ?, set_number = ?, status = ?, active = ?  WHERE booking_id= ?', [
            $request->customer_id, $request->screening_id, $request->set_number, $request->status, $request->active, $id]);

        return response()->json([
            'status' => 'success',
            'message' => 'Booking updated successfully'
        ], 200);
    }

    public function delete($id)
    {
        $booking= DB::delete('DELETE FROM booking WHERE booking_id = ?', [$id]);
        return response()->json([
            'status' => 'success',
            'message' => 'Booking deleted successfully'
        ], 200);
    }

    public function updateActive($id)
    {
        $booking = DB::update('UPDATE booking SET active = 0 WHERE booking_id = ?', [$id]);
        return response()->json([
            'status' => 'success',
            'message' => 'Booking updated successfully'
        ], 200);
    }

    public function updateInActive($id)
    {
        $booking = DB::update('UPDATE booking SET active = 1 WHERE booking_id = ?', [$id]);
        return response()->json([
            'status' => 'success',
            'message' => 'Booking updated successfully'
        ], 200);
    }

    public function active()
    {
        $booking = DB::select('SELECT * FROM booking where active = 1');
        return response()->json([
            'status' => 'success',
            'rows' => count($booking),
            'data' => $booking
        ], 200);
    }

    public function inActive()
    {
        $booking = DB::select('SELECT * FROM booking where active = 0');
        return response()->json([
            'status' => 'success',
            'rows' => count($booking),
            'data' => $booking
        ], 200);
    }
}
