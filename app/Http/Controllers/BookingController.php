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

    public function dataTables(Request $request)
    {
        try {
            $activeFilter = $request->input('active_filter', '');
            $statusFilter = $request->input('status_filter', '');
            
            // Create base query with explicit column selection and proper quotes
            $query = DB::table('booking as b')
                ->join('customer as c', 'b.customer_id', '=', 'c.customer_id')
                ->join('screenings as s', 'b.screening_id', '=', 's.screening_id')
                ->select([
                    DB::raw('b.booking_id as booking_id'),
                    DB::raw('b.customer_id as customer_id'),
                    DB::raw('b.screening_id as screening_id'),
                    DB::raw('CONCAT(c.first_name, " ", c.last_name) as customer_name'),
                    DB::raw('b.set_number as seats'),
                    DB::raw('b.status as status'),
                    DB::raw('b.active as active'),
                    DB::raw('b.created_at as created_at'),
                    DB::raw('b.updated_at as updated_at'),
                    DB::raw('s.screening_time as screening_time')
                ]);
            
            // Apply active/inactive filter
            if ($activeFilter === 'active') {
                $query->where('b.active', '=', 1);
            } elseif ($activeFilter === 'inactive') {
                $query->where('b.active', '=', 0);
            }

            // Apply status filter
            if ($statusFilter === 'confirmed' || $statusFilter === 'cancelled') {
                $query->where('b.status', '=', $statusFilter);
            }

            // Enable query logging for debugging
            \DB::enableQueryLog();
            
            $result = DataTables::of($query)
                ->addColumn('action', function($row) {
                    $buttons = '';
                    
                    if ($row->active == 1) {
                        $buttons .= '<i class="fas fa-edit edit-booking cursor-pointer mx-1" data-id="'.$row->booking_id.'"></i>';
                        $buttons .= '<i class="fas fa-trash delete-booking cursor-pointer mx-1" data-id="'.$row->booking_id.'"></i>';
                    } else {
                        $buttons .= '<i class="fas fa-undo restore-booking cursor-pointer mx-1" data-id="'.$row->booking_id.'"></i>';
                    }
                    
                    return $buttons;
                })
                ->editColumn('screening_time', function($row) {
                    return $row->screening_time ? date('Y-m-d H:i:s', strtotime($row->screening_time)) : '';
                })
                ->editColumn('active', function($row) {
                    return $row->active == 1 ? 'Active' : 'Inactive';
                })
                ->rawColumns(['action'])
                ->make(true);

            // Log the actual query that was executed
            \Log::info('Executed query:', \DB::getQueryLog());
            
            return $result;

        } catch (\Exception $e) {
            \Log::error('DataTables Error:', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'last_query' => \DB::getQueryLog()
            ]);
            
            return response()->json([
                'error' => true,
                'message' => 'Error loading bookings: ' . $e->getMessage()
            ], 500);
        }
    }

    public function store(Request $request)
    {
        $request->validate([
            'customer_id' => 'required|exists:customer,customer_id',
            'screening_id' => 'required|exists:screenings,screening_id',
            'set_number' => 'required|string|max:255',
        ]);

        // Check if screening is active
        $screening = DB::select('
            SELECT * FROM screenings 
            WHERE screening_id = ? AND active = 1', 
            [$request->screening_id]
        );

        if (empty($screening)) {
            return response()->json([
                'success' => false,
                'message' => 'Selected screening is not active.'
            ], 422);
        }

        // Check if customer is active
        $customer = DB::select('
            SELECT * FROM customer 
            WHERE customer_id = ? AND active = 1', 
            [$request->customer_id]
        );

        if (empty($customer)) {
            return response()->json([
                'success' => false,
                'message' => 'Selected customer is not active.'
            ], 422);
        }

        // Check if booking with same customer and screening exists
        $existingBooking = DB::select('
            SELECT * FROM booking 
            WHERE customer_id = ? AND screening_id = ? AND active = 1', 
            [$request->customer_id, $request->screening_id]
        );

        if (!empty($existingBooking)) {
            return response()->json([
                'success' => false,
                'message' => 'A booking already exists for this customer and screening.'
            ], 422);
        }

        // Insert new booking
        DB::insert('
            INSERT INTO booking (customer_id, screening_id, set_number, status, active, created_at, updated_at) 
            VALUES (?, ?, ?, ?, ?, ?, ?)', 
            [
                $request->customer_id,
                $request->screening_id,
                $request->set_number,
                'confirmed',   // or whatever default status you want
                1,
                now(),         // PHP's current timestamp
                now()
            ]
        );

        return response()->json([
            'success' => true,
            'message' => 'Booking added successfully'
        ]);
    }

    public function updateStatus($id)
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

            // Update the active status to 0
            DB::update('UPDATE booking SET active = 0, updated_at = NOW() WHERE booking_id = ?', [$id]);

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
            // Validate the request
            $validated = $request->validate([
                'customer_id' => 'required|exists:customer,customer_id',
                'screening_id' => 'required|exists:screenings,screening_id',
                'set_number' => 'required|string|max:255',
                'status' => 'required|string|in:confirmed,cancelled'
            ]);

            // Get current booking
            $currentBooking = DB::select('SELECT * FROM booking WHERE booking_id = ?', [$id]);
            if (empty($currentBooking)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Booking not found'
                ], 404);
            }

            $currentBooking = $currentBooking[0];

            // Check if any data is actually changing
            if ($currentBooking->customer_id == $validated['customer_id'] && 
                $currentBooking->screening_id == $validated['screening_id'] && 
                $currentBooking->set_number == $validated['set_number'] &&
                $currentBooking->status == $validated['status']) {
                return response()->json([
                    'success' => true,
                    'message' => 'No changes were made to the booking.'
                ]);
            }

            // Check if screening is active
            $screening = DB::select('
                SELECT * FROM screenings 
                WHERE screening_id = ? AND active = 1', 
                [$validated['screening_id']]
            );

            if (empty($screening)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Selected screening is not active.'
                ], 422);
            }

            // Check if customer is active
            $customer = DB::select('
                SELECT * FROM customer 
                WHERE customer_id = ? AND active = 1', 
                [$validated['customer_id']]
            );

            if (empty($customer)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Selected customer is not active.'
                ], 422);
            }

            // Check if another booking exists with same customer and screening (excluding current booking)
            if ($currentBooking->customer_id != $validated['customer_id'] || 
                $currentBooking->screening_id != $validated['screening_id']) {
                
                $existingBooking = DB::select('
                    SELECT * FROM booking 
                    WHERE customer_id = ? 
                    AND screening_id = ? 
                    AND active = 1 
                    AND booking_id != ?',
                    [
                        $validated['customer_id'],
                        $validated['screening_id'],
                        $id
                    ]
                );

                if (!empty($existingBooking)) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Another booking already exists for this customer and screening.'
                    ], 422);
                }
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
                    $validated['customer_id'],
                    $validated['screening_id'],
                    $validated['set_number'],
                    $validated['status'],
                    $id
                ]
            );

            return response()->json([
                'success' => true,
                'message' => 'Booking updated successfully'
            ]);

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

            $booking = $booking[0];

            // Check if the associated screening is still active
            $screening = DB::select('
                SELECT s.* FROM screenings s
                WHERE s.screening_id = ? AND s.active = 1',
                [$booking->screening_id]
            );

            if (empty($screening)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot restore booking: Associated screening is not active'
                ], 422);
            }

            // Check if the associated customer is still active
            $customer = DB::select('
                SELECT c.* FROM customer c
                WHERE c.customer_id = ? AND c.active = 1',
                [$booking->customer_id]
            );

            if (empty($customer)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot restore booking: Associated customer is not active'
                ], 422);
            }

            // Update the active status to 1 and set status back to confirmed
            DB::update('
                UPDATE booking 
                SET active = 1, 
                    status = "confirmed",
                    updated_at = NOW() 
                WHERE booking_id = ?', 
                [$id]
            );

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
