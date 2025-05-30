<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\DataTables;
use Illuminate\Http\Request;

class CustomerController extends Controller
{
    public function list() {

        return view('customer.list');
    }

    public function dataTables(Request $request)
    {
        $filter = $request->input('filter', '');
        
        $query = DB::table('customer');

        // Apply filter
        if ($filter === 'active') {
            $query->where('active', 1);
        } elseif ($filter === 'inactive') {
            $query->where('active', 0);
        }

        return DataTables::of($query)->make(true);
    }

    public function store(Request $request)
    {
        $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|string|max:255',
             'phonenumber' => 'required|string|max:255',
        ]);

        $existingCustomer = DB::table('customer')
            ->whereRaw('LOWER(email) = ?', [strtolower($request->email)])
            ->exists();

        if ($existingCustomer) {
            return response()->json([
                'message' => 'Email already exists.'
            ], 422);
        }

        $existingCustomerFnameAndLname = DB::table('customer')
        ->whereRaw('LOWER(first_name) = ? AND LOWER(last_name) = ?', [strtolower($request->first_name), strtolower($request->last_name)])
        ->exists();

        if ($existingCustomerFnameAndLname) {
            return response()->json([
                'message' => 'A Customer with the same firstname and lastname already exists.'
            ], 422);
        }

        // Check if mall with same fnamae, lname, email  and phono number exists (case-insensitive)
        $existingCustomer = DB::select('
            SELECT * FROM customer 
            WHERE LOWER(first_name) = ? AND LOWER(last_name) = ? AND LOWER(email) = ? AND LOWER(phonenumber) = ?', 
            [strtolower($request->first_name), strtolower($request->last_name), strtolower($request->email), strtolower($request->phonenumber)]
        );

        if (!empty($existingCustomer)) {
            return response()->json([
                'message' => 'A Customer with the same firstname, lastname, email and Phone number already exists.'
            ], 422);
        }

        // Insert new mall
        DB::insert('INSERT INTO customer (first_name, last_name, email, phonenumber , created_at, updated_at) VALUES (?, ?, ?, ?, NOW(), NOW())', [
            $request->first_name,
            $request->last_name,
            $request->email,
            $request->phonenumber,
        ]);

        return response()->json(['message' => 'Customer added successfully']);
    }

    public function updateStatus($id)
    {
        try {
            $customer = DB::select('SELECT * FROM customer WHERE customer_id = ?', [$id]);
            
            if (empty($customer)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Customer not found'
                ], 404);
            }

            // Check if customer has any active bookings
            $activeBookings = DB::select('SELECT * FROM booking WHERE customer_id = ? AND active = 1', [$id]);
            if (!empty($activeBookings)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot deactivate customer: It has active bookings'
                ], 422);
            }

            // Update the active status to 0
            DB::update('UPDATE customer SET active = 0 WHERE customer_id = ?', [$id]);

            return response()->json([
                'success' => true,
                'message' => 'Customer has been deactivated successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to deactivate customer: ' . $e->getMessage()
            ], 500);
        }
    }

    public function update($id, Request $request)
    {
        try {
            $request->validate([
                'first_name' => 'required|string|max:255',
                'last_name' => 'required|string|max:255',
                'email' => 'required|string|max:255',
                'phonenumber' => 'required|string|max:255',
            ]);

            // Check if manager exists
            $customer = DB::select('SELECT * FROM customer WHERE customer_id = ?', [$id]);
            if (empty($customer)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Customer not found'
                ], 404);
            }
            
            // Check if another customer has the same details (excluding current customer)
            $existingCustomer = DB::select('
                SELECT * FROM customer 
                WHERE LOWER(first_name) = ? AND LOWER(last_name) = ? AND LOWER(email) = ? AND LOWER(phonenumber) = ? AND customer_id != ?',
                [
                    strtolower($request->first_name), 
                    strtolower($request->last_name), 
                    strtolower($request->email),
                    strtolower($request->phonenumber),
                    $id
                ]
            );

            if (!empty($existingCustomer)) {
                return response()->json([
                    'message' => 'Another customer with the same details already exists.'
                ], 422);
            }

             // Check if another customer has the same email (excluding current customer)
             $existingCustomerEmail = DB::select('
                SELECT * FROM customer 
                WHERE LOWER(email) = ? AND customer_id != ?',
                [
                    strtolower($request->email), 
                    $id
                ]
            );

            if (!empty($existingCustomerEmail)) {
                return response()->json([
                    'message' => 'Another customer with the same email already exists.'
                ], 422);
            }

             // Check if another customer has the same firstname and lastname (excluding current customer)
             $existingCustomerFnameAndLname = DB::select('
                SELECT * FROM customer 
                WHERE LOWER(first_name) = ? AND LOWER(last_name) = ? AND customer_id != ?',
                [
                    strtolower($request->first_name), 
                    strtolower($request->last_name), 
                    $id
                ]
            );

            if (!empty($existingCustomerFnameAndLname)) {
                return response()->json([
                    'message' => 'Another customer with the same firstname and lastname already exists.'
                ], 422);
            }

            // Update the manager
            DB::update('
                UPDATE customer 
                SET first_name = ?, last_name = ?, email = ?, phonenumber = ?, updated_at = NOW() 
                WHERE customer_id = ?',
                [
                    $request->first_name,
                    $request->last_name,
                    $request->email,
                    $request->phonenumber,
                    $id
                ]
            );

            return response()->json([
                'success' => true,
                'message' => 'Customer updated successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update customer: ' . $e->getMessage()
            ], 500);
        }
    }

    public function restore($id)
    {
        try {
            // Check if customer exists
            $customer = DB::select('SELECT * FROM customer WHERE customer_id = ?', [$id]);
            
            if (empty($customer)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Customer not found'
                ], 404);
            }

            // Update the active status to 1
            DB::update('UPDATE customer SET active = 1 WHERE customer_id = ?', [$id]);

            return response()->json([
                'success' => true,
                'message' => 'Customer has been restored successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to restore customer: ' . $e->getMessage()
            ], 500);
        }
    }
}
