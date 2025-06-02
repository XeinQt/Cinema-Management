<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;


class MallsController extends Controller
{
    
    public function list()
    {
        $malls = DB::select('SELECT * FROM malls');
        return response()->json([
            'status' => 'success',
            'rows' => count($malls),
            'data' => $malls
        ], 200);
    }

    public function seacrhById($id)
    {
        $malls = DB::select('SELECT * FROM malls WHERE mall_id = ?', [$id]);
        return response()->json([
            'status' => 'success',
            'data' => $malls
        ], 200);
        
    }

    
    public function create(Request $request)
    {
        $malls = DB::insert('INSERT INTO malls (name, location, description, active) VALUES (?, ?, ?, ?)', [
            $request->name,
            $request->location,
            $request->description,
            $request->active
        ]);
        
        return response()->json([
            'status' => 'success',
            'message' => 'Malls created successfully'
        ], 200);
    }

    public function update(Request $request, $id)
    {
        $malls = DB::update('UPDATE malls SET name = ?, location = ?, description = ?, active = ? WHERE mall_id = ?', [
            $request->name, $request->location, $request->description, $request->active, $id]);

        return response()->json([
            'status' => 'success',
            'message' => 'Malls updated successfully'
        ], 200);
    }

    public function delete($id)
    {
        $malls = DB::delete('DELETE FROM malls WHERE mall_id = ?', [$id]);
        return response()->json([
            'status' => 'success',
            'message' => 'Malls deleted successfully'
        ], 200);
    }

    public function updateActive($id)
    {
        $malls = DB::update('UPDATE malls SET active = 0 WHERE mall_id = ?', [$id]);
        return response()->json([
            'status' => 'success',
            'message' => 'Customer updated successfully'
        ], 200);
    }

    public function updateInActive($id)
    {
        $malls = DB::update('UPDATE malls SET active = 1 WHERE mall_id = ?', [$id]);
        return response()->json([
            'status' => 'success',
            'message' => 'Malls updated successfully'
        ], 200);
    }

   public function active()
    {
        $malls = DB::select('SELECT * FROM malls where active = 1');
        return response()->json([
            'status' => 'success',
            'rows' => count($malls),
            'data' => $malls
        ], 200);
    }

    public function inactive()
    {
        $malls = DB::select('SELECT * FROM malls where active = 0');
        return response()->json([
            'status' => 'success',
            'rows' => count($malls),
            'data' => $malls
        ], 200);
    }
}
