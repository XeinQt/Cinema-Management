<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ScreeningController extends Controller
{
    public function list()
    {
        $screenings = DB::select('SELECT * FROM screenings');
        return response()->json([
            'status' => 'success',
            'rows' => count($screenings),
            'data' => $screenings
        ], 200);
    }

    public function seacrhById($id)
    {
        $screenings = DB::select('SELECT * FROM screenings WHERE screening_id = ?', [$id]);
        return response()->json([
            'status' => 'success',
            'data' => $screenings
        ], 200);
        
    }

    public function create(Request $request)
    {
        $screenings = DB::insert('INSERT INTO screenings (cinema_id, movie_id, screening_time, active) VALUES (?, ?, ?, ?)', [
            $request->cinema_id,
            $request->movie_id,
            $request->screening_time,
            $request->active
        ]);
        
        return response()->json([
            'status' => 'success',
            'message' => 'Screening created successfully'
        ], 200);
    }

    public function update(Request $request, $id)
    {
        $screenings = DB::update('UPDATE screenings SET cinema_id = ?, movie_id = ?, screening_time = ?, active = ?  WHERE screening_id = ?', [
                $request->cinema_id, 
                $request->movie_id, 
                $request->screening_time,
                $request->active, 
            $id]);

        return response()->json([
            'status' => 'success',
            'message' => 'Screening updated successfully'
        ], 200);
    }

    public function delete($id)
    {
        $screenings= DB::delete('DELETE FROM screenings WHERE screening_id = ?', [$id]);
        return response()->json([
            'status' => 'success',
            'message' => 'Screening deleted successfully'
        ], 200);
    }

    public function updateActive($id)
    {
        $screenings = DB::update('UPDATE screenings SET active = 0 WHERE screening_id = ?', [$id]);
        return response()->json([
            'status' => 'success',
            'message' => 'Screening updated successfully'
        ], 200);
    }

    public function updateInActive($id)
    {
        $screenings = DB::update('UPDATE screenings SET active = 1 WHERE screening_id = ?', [$id]);
        return response()->json([
            'status' => 'success',
            'message' => 'Screening updated successfully'
        ], 200);
    }

    public function active()
    {
        $screenings = DB::select('SELECT * FROM screenings where active = 1');
        return response()->json([
            'status' => 'success',
            'rows' => count($screenings),
            'data' => $screenings
        ], 200);
    }

    public function inActive()
    {
        $screenings = DB::select('SELECT * FROM screenings where active = 0');
        return response()->json([
            'status' => 'success',
            'rows' => count($screenings),
            'data' => $screenings
        ], 200);
    }
}
