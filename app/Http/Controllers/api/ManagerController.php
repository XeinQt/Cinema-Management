<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ManagerController extends Controller
{
    public function list()
    {
        $managers = DB::select('SELECT * FROM managers');
        return response()->json([
            'status' => 'success',
            'rows' => count($managers),
            'data' => $managers
        ], 200);
    }

    public function seacrhById($id)
    {
        $managers = DB::select('SELECT * FROM managers WHERE manager_id = ?', [$id]);
        return response()->json([
            'status' => 'success',
            'data' => $managers
        ], 200);
        
    }

    
    public function create(Request $request)
    {
        $managers = DB::insert('INSERT INTO managers (first_name, last_name, email, phonenumber, active) VALUES (?, ?, ?, ?, ?)', [
            $request->first_name,
            $request->last_name,
            $request->email,
            $request->phonenumber,
            $request->active
        ]);
        
        return response()->json([
            'status' => 'success',
            'message' => 'Manager created successfully'
        ], 200);
    }

    public function update(Request $request, $id)
    {
        $managers = DB::update('UPDATE managers SET first_name = ?, last_name = ?, email = ?, phonenumber = ?, active = ? WHERE manager_id = ?', [
             $request->first_name,
            $request->last_name,
            $request->email,
            $request->phonenumber,
            $request->active
        , $id]);

        return response()->json([
            'status' => 'success',
            'message' => 'Managers updated successfully'
        ], 200);
    }

    public function delete($id)
    {
        $managers = DB::delete('DELETE FROM managers WHERE manager_id = ?', [$id]);
        return response()->json([
            'status' => 'success',
            'message' => 'Manager deleted successfully'
        ], 200);
    }

    public function updateActive($id)
    {
        $managers = DB::update('UPDATE managers SET active = 0 WHERE manager_id = ?', [$id]);
        return response()->json([
            'status' => 'success',
            'message' => 'Managers updated successfully'
        ], 200);
    }

    public function updateInActive($id)
    {
        $managers = DB::update('UPDATE managers SET active = 1 WHERE manager_id = ?', [$id]);
        return response()->json([
            'status' => 'success',
            'message' => 'Managers updated successfully'
        ], 200);
    }

    public function active()
    {
        $managers = DB::select('SELECT * FROM managers where active = 1');
        return response()->json([
            'status' => 'success',
            'rows' => count($managers),
            'data' => $managers
        ], 200);
    }

    public function inactive()
    {
        $managers = DB::select('SELECT * FROM managers where active = 0');
        return response()->json([
            'status' => 'success',
            'rows' => count($managers),
            'data' => $managers
        ], 200);
    }
}
