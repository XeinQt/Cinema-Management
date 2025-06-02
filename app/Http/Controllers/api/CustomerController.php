<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CustomerController extends Controller
{
    public function list()
    {
        $customers = DB::select('SELECT * FROM customer');
        return response()->json([
            'status' => 'success',
            'rows' => count($customers),
            'data' => $customers
        ], 200);
    }

    public function seacrhById($id)
    {
        $customer = DB::select('SELECT * FROM customer WHERE customer_id = ?', [$id]);
        return response()->json([
            'status' => 'success',
            'data' => $customer
        ], 200);
        
    }

    public function create(Request $request)
    {
        $customer = DB::insert('INSERT INTO customer (first_name, last_name, email, phonenumber) VALUES (?, ?, ?, ?)', [
            $request->first_name,
            $request->last_name,
            $request->email,
            $request->phonenumber
        ]);
        
        return response()->json([
            'status' => 'success',
            'message' => 'Customer created successfully'
        ], 200);
    }

    public function update(Request $request, $id)
    {
        $customer = DB::update('UPDATE customer SET first_name = ?, last_name = ?, email = ?, phonenumber = ? WHERE customer_id = ?', [
            $request->first_name, $request->last_name, $request->email, $request->phonenumber, $id]);

        return response()->json([
            'status' => 'success',
            'message' => 'Customer updated successfully'
        ], 200);
    }

    public function delete($id)
    {
        $customer = DB::delete('DELETE FROM customer WHERE customer_id = ?', [$id]);
        return response()->json([
            'status' => 'success',
            'message' => 'Customer deleted successfully'
        ], 200);
    }

    public function updateActive($id)
    {
        $customer = DB::update('UPDATE customer SET active = 0 WHERE customer_id = ?', [$id]);
        return response()->json([
            'status' => 'success',
            'message' => 'Customer updated successfully'
        ], 200);
    }

    public function updateInActive($id)
    {
        $customer = DB::update('UPDATE customer SET active = 1 WHERE customer_id = ?', [$id]);
        return response()->json([
            'status' => 'success',
            'message' => 'Customer updated successfully'
        ], 200);
    }

    public function active()
    {
        $customers = DB::select('SELECT * FROM customer where active = 1');
        return response()->json([
            'status' => 'success',
            'rows' => count($customers),
            'data' => $customers
        ], 200);
    }

    public function inActive()
    {
        $customers = DB::select('SELECT * FROM customer where active = 0');
        return response()->json([
            'status' => 'success',
            'rows' => count($customers),
            'data' => $customers
        ], 200);
    }
}
