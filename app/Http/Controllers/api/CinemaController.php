<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CinemaController extends Controller
{
    public function list()
    {
        $cinemas = DB::select('SELECT * FROM cinemas');
        return response()->json([
            'status' => 'success',
            'rows' => count($cinemas),
            'data' => $cinemas
        ], 200);
    }

    public function seacrhById($id)
    {
        $cinemas = DB::select('SELECT * FROM cinemas WHERE cinema_id = ?', [$id]);
        return response()->json([
            'status' => 'success',
            'data' => $cinemas
        ], 200);
        
    }

     public function create(Request $request)
    {
        $cinemas = DB::insert('INSERT INTO cinemas (mall_id, manager_id, name, active) VALUES (?, ?, ?, ?)', [
            $request->mall_id,
            $request->manager_id,
            $request->name,
            $request->active
        ]);
        
        return response()->json([
            'status' => 'success',
            'message' => 'Cinema created successfully'
        ], 200);
    }

      public function update(Request $request, $id)
    {
        $cinemas = DB::update('UPDATE cinemas SET mall_id = ?, manager_id = ?, name = ?, active = ? WHERE cinema_id = ?', [
            $request->mall_id, $request->manager_id, $request->name, $request->active, $id]);

        return response()->json([
            'status' => 'success',
            'message' => 'Cinema updated successfully'
        ], 200);
    }
     public function delete($id)
    {
        $cinemas = DB::delete('DELETE FROM cinemas WHERE cinema_id = ?', [$id]);
        return response()->json([
            'status' => 'success',
            'message' => 'Cinema deleted successfully'
        ], 200);
    }
     public function updateActive($id)
    {
        $cinemas = DB::update('UPDATE cinemas SET active = 0 WHERE cinema_id = ?', [$id]);
        return response()->json([
            'status' => 'success',
            'message' => 'Cinema updated successfully'
        ], 200);
    }

      public function updateInActive($id)
    {
        $cinemas = DB::update('UPDATE cinemas SET active = 1 WHERE cinema_id = ?', [$id]);
        return response()->json([
            'status' => 'success',
            'message' => 'Cinema updated successfully'
        ], 200);
    }

     public function active()
    {
        $cinemas = DB::select('SELECT * FROM cinemas where active = 1');
        return response()->json([
            'status' => 'success',
            'rows' => count($cinemas),
            'data' => $cinemas
        ], 200);
    }

      public function inActive()
    {
        $cinemas = DB::select('SELECT * FROM cinemas where active = 0');
        return response()->json([
            'status' => 'success',
            'rows' => count($cinemas),
            'data' => $cinemas
        ], 200);
    }
}
