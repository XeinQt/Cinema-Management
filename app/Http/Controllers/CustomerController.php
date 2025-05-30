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
        
        $query = 'SELECT * FROM customer';
        
        // Apply filter
        if ($filter === 'active') {
            $query .= ' WHERE active = 1';
        } elseif ($filter === 'inactive') {
            $query .= ' WHERE active = 0';
        }
        
        $query .= ' ORDER BY customer_id DESC';
        
        $customers = DB::select($query);
        return DataTables::of($customers)
            ->addColumn('action', function($row) {
                $buttons = '';
                
                if ($row->active == 1) {
                    $buttons .= '<i class="fas fa-edit edit-customer" data-id="'.$row->customer_id.'"></i>';
                    $buttons .= '<i class="fas fa-trash delete-customer" data-id="'.$row->customer_id.'"></i>';
                } else {
                    $buttons .= '<i class="fas fa-undo restore-customer" data-id="'.$row->customer_id.'"></i>';
                }
                
                return $buttons;
            })
            ->rawColumns(['action'])
            ->make(true);
    }

    public function store(Request $request)
    {
        $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|string|max:255',
            'phonenumber' => 'required|string|max:255',
        ]);

        $existingCustomers = DB::table('customer')
            ->whereRaw('LOWER(email) = ?', [strtolower($request->email)])
            ->exists();

        if ($existingCustomers) {
            return response()->json([
                'message' => 'Email already exists.'
            ], 422);
        }

        // Check if customer with same first name and last name exists
        $existingCustomerName = DB::select('
            SELECT * FROM customer 
            WHERE LOWER(first_name) = ? AND LOWER(last_name) = ?', 
            [strtolower($request->first_name), strtolower($request->last_name)]
        );

        if (!empty($existingCustomerName)) {
            return response()->json([
                'message' => 'A Customer with the same firstname and lastname already exists.'
            ], 422);
        }

        // Check if customer with all same details exists
        $existingCustomer = DB::select('
            SELECT * FROM customer 
            WHERE LOWER(first_name) = ? AND LOWER(last_name) = ? AND LOWER(email) = ? AND LOWER(phonenumber) = ?', 
            [strtolower($request->first_name), strtolower($request->last_name), strtolower($request->email), strtolower($request->phonenumber)]
        );

        if (!empty($existingCustomer)) {
            return response()->json([
                'message' => 'A Customer with the same details already exists.'
            ], 422);
        }

        // Insert new customer
        DB::insert('INSERT INTO customer (first_name, last_name, email, phonenumber, active, created_at, updated_at) VALUES (?, ?, ?, ?, ?, NOW(), NOW())', [
            $request->first_name,
            $request->last_name,
            $request->email,
            $request->phonenumber,
            1
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Customer added successfully'
        ]);
    }

    public function updateStatus($id)
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

            // Check if customer has any active bookings
            $activeBookings = DB::select('SELECT * FROM booking WHERE customer_id = ? AND active = 1', [$id]);
            if (!empty($activeBookings)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot deactivate customer: They have active bookings'
                ], 422);
            }

            // Update the active status to 0
            DB::update('UPDATE customer SET active = 0, updated_at = NOW() WHERE customer_id = ?', [$id]);

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
                'email' => 'required|string|max:255|email',
                'phonenumber' => 'required|string|max:255',
            ]);

            // Get current customer
            $currentCustomer = DB::select('SELECT * FROM customer WHERE customer_id = ?', [$id]);
            if (empty($currentCustomer)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Customer not found'
                ], 404);
            }

            $currentCustomer = $currentCustomer[0];

            // Check if any data is actually changing
            if ($currentCustomer->first_name === $request->first_name && 
                $currentCustomer->last_name === $request->last_name && 
                $currentCustomer->email === $request->email && 
                $currentCustomer->phonenumber === $request->phonenumber) {
                return response()->json([
                    'success' => true,
                    'message' => 'No changes were made to the customer.'
                ]);
            }

            // Check if email is being changed
            if (strtolower($currentCustomer->email) !== strtolower($request->email)) {
                $existingCustomerEmail = DB::select('
                    SELECT * FROM customer 
                    WHERE LOWER(email) = ? 
                    AND customer_id != ?',
                    [
                        strtolower($request->email),
                        $id
                    ]
                );

                if (!empty($existingCustomerEmail)) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Another customer with the same email already exists.'
                    ], 422);
                }
            }

            // Check if name combination is being changed
            if (strtolower($currentCustomer->first_name) !== strtolower($request->first_name) || 
                strtolower($currentCustomer->last_name) !== strtolower($request->last_name)) {
                
                $existingCustomerName = DB::select('
                    SELECT * FROM customer 
                    WHERE LOWER(first_name) = ? 
                    AND LOWER(last_name) = ? 
                    AND customer_id != ?',
                    [
                        strtolower($request->first_name),
                        strtolower($request->last_name),
                        $id
                    ]
                );

                if (!empty($existingCustomerName)) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Another customer with the same first name and last name already exists.'
                    ], 422);
                }
            }

            // Update the customer
            DB::update('
                UPDATE customer 
                SET first_name = ?, 
                    last_name = ?, 
                    email = ?, 
                    phonenumber = ?, 
                    updated_at = NOW() 
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
            DB::update('UPDATE customer SET active = 1, updated_at = NOW() WHERE customer_id = ?', [$id]);

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
