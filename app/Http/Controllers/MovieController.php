<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\DataTables;

class MovieController extends Controller
{
    public function list () {
        return view ('movies.list');
    }

    public function dataTables ()
    {
      $movies = DB::select('SELECT * FROM movies');

       return DataTables::of($movies)->make(true);
    }

     public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'genre' => 'required|string|max:255',
            'duration' => 'required|string|max:255',
            'description' => 'required|string|max:255',
            'rating' => 'required',
        ]);

        // Check if mall with same fnamae, lname, email  and phono number exists (case-insensitive)
        $existingMovie = DB::select('
            SELECT * FROM movies 
            WHERE LOWER(title) = ? AND LOWER(genre) = ? AND LOWER(duration) = ? AND LOWER(description) = ? AND LOWER(rating) = ?', 
            [strtolower($request->title), strtolower($request->genre), strtolower($request->duration), strtolower($request->description) , strtolower($request->rating)]
        );

        if (!empty($existingMovie)) {
            return response()->json([
                'message' => 'A Movues with the same title, genre, duration, description and rating already exists.'
            ], 422);
        }

        // Insert new mall
        DB::insert('INSERT INTO movies (title, genre, duration, description , rating,  created_at, updated_at) VALUES (?, ?, ?, ?, ?, NOW(), NOW())', [
            $request->title,
            $request->genre,
            $request->duration,
            $request->description,
            $request->rating,
        ]);

        return response()->json(['message' => 'Movies added successfully']);
    }
}
