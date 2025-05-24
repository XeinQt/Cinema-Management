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

      public function dataTables ()
    {
       $customer = DB::SELECT('SELECT * FROM customer');
       return DataTables::of($customer)->make(true);
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

}
