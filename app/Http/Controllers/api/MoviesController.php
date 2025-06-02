<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MoviesController extends Controller
{
   public function list()
    {
        $movies = DB::select('SELECT * FROM movies');
        return response()->json([
            'status' => 'success',
            'rows' => count($movies),
            'data' => $movies
        ], 200);
    }

    public function seacrhById($id)
    {
        $movies = DB::select('SELECT * FROM movies WHERE movie_id = ?', [$id]);
        return response()->json([
            'status' => 'success',
            'data' => $movies
        ], 200);
        
    }

    public function create(Request $request)
    {
        $movies = DB::insert('INSERT INTO movies (title, genre, duration, description, rating, active) VALUES (?, ?, ?, ?, ?, ?)', [
            $request->title,
            $request->genre,
            $request->duration,
            $request->description,
            $request->rating,
            $request->active,
        ]);
        
        return response()->json([
            'status' => 'success',
            'message' => 'Movie created successfully'
        ], 200);
    }

    public function update(Request $request, $id)
    {
        $movies = DB::update('UPDATE movies SET title = ?, genre = ?, duration = ?, description = ?, rating = ?, active = ?  WHERE movie_id = ?', [
            $request->tiltle, $request->genre, $request->duration, $request->description,$request->rating,$request->active, $id]);

        return response()->json([
            'status' => 'success',
            'message' => 'Movie updated successfully'
        ], 200);
    }

    public function delete($id)
    {
        $movies = DB::delete('DELETE FROM movies WHERE movie_id = ?', [$id]);
        return response()->json([
            'status' => 'success',
            'message' => 'Movie deleted successfully'
        ], 200);
    }

    public function updateActive($id)
    {
        $movies = DB::update('UPDATE movies SET active = 0 WHERE movie_id = ?', [$id]);
        return response()->json([
            'status' => 'success',
            'message' => 'Movie updated successfully'
        ], 200);
    }

    public function updateInActive($id)
    {
        $movies = DB::update('UPDATE movies SET active = 1 WHERE movie_id = ?', [$id]);
        return response()->json([
            'status' => 'success',
            'message' => 'Movie updated successfully'
        ], 200);
    }

    public function active()
    {
        $movie = DB::select('SELECT * FROM movie where active = 1');
        return response()->json([
            'status' => 'success',
            'rows' => count($movie),
            'data' => $movie
        ], 200);
    }

    public function inActive()
    {
        $movie = DB::select('SELECT * FROM movie where active = 0');
        return response()->json([
            'status' => 'success',
            'rows' => count($movie),
            'data' => $movie
        ], 200);
    }
}
