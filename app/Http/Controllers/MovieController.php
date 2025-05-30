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

    public function dataTables(Request $request)
    {
        $filter = $request->input('filter', '');
        
        $query = DB::table('movies');

        // Apply filter
        if ($filter === 'active') {
            $query->where('active', 1);
        } elseif ($filter === 'inactive') {
            $query->where('active', 0);
        }

        $query->select([
            'movie_id',
            'title',
            'genre',
            'duration',
            'description',
            'rating',
            'active',
            'created_at',
            'updated_at'
        ]);

        return DataTables::of($query)
            ->addIndexColumn()
            ->make(true);
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
        $existingMovie = DB::select('
            SELECT * FROM movies 
            WHERE LOWER(title) = ? AND LOWER(genre) = ? AND LOWER(duration) = ? AND LOWER(description) = ? AND LOWER(rating) = ? AND active = 1', 
            [strtolower($request->title), strtolower($request->genre), strtolower($request->duration), strtolower($request->description) , strtolower($request->rating)]
        );

        if (!empty($existingMovie)) {
            return response()->json([
                'message' => 'A Movie with the same title, genre, duration, description and rating already exists.'
            ], 422);
        }

        // Insert new movie
        DB::insert('INSERT INTO movies (title, genre, duration, description, rating, active, created_at, updated_at) VALUES (?, ?, ?, ?, ?, 1, NOW(), NOW())', [
            $request->title,
            $request->genre,
            $request->duration,
            $request->description,
            $request->rating,
        ]);

        return response()->json(['message' => 'Movie added successfully']);
    }

    public function updateStatus($id)
    {
        try {
            // Check if movie exists
            $movie = DB::select('SELECT * FROM movies WHERE movie_id = ?', [$id]);
            
            if (empty($movie)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Movie not found'
                ], 404);
            }

            // Check if movie has any active screenings
            $activeScreenings = DB::select('SELECT * FROM screenings WHERE movie_id = ? AND active = 1', [$id]);
            if (!empty($activeScreenings)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot deactivate movie: It has active screenings scheduled'
                ], 422);
            }

            // Update the active status to 0
            DB::update('UPDATE movies SET active = 0 WHERE movie_id = ?', [$id]);

            return response()->json([
                'success' => true,
                'message' => 'Movie has been deactivated successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to deactivate movie: ' . $e->getMessage()
            ], 500);
        }
    }

    public function restore($id)
    {
        try {
            // Check if movie exists
            $movie = DB::select('SELECT * FROM movies WHERE movie_id = ?', [$id]);
            
            if (empty($movie)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Movie not found'
                ], 404);
            }

            // Update the active status to 1
            DB::update('UPDATE movies SET active = 1 WHERE movie_id = ?', [$id]);

            return response()->json([
                'success' => true,
                'message' => 'Movie has been restored successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to restore movie: ' . $e->getMessage()
            ], 500);
        }
    }
    
    public function update($id, Request $request)
    {
        try {
            $request->validate([
                'title' => 'required|string|max:255',
                'genre' => 'required|string|max:255',
                'duration' => 'required|string|max:255',
                'description' => 'required|string|max:255',
                'rating' => 'required',
            ]);

            // Check if movie exists
            $movie = DB::select('SELECT * FROM movies WHERE movie_id = ?', [$id]);
            if (empty($movie)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Movie not found'
                ], 404);
            }
            
            // Check if another movie has the same details (excluding current movie)
            $existingMovie = DB::select('
                SELECT * FROM movies 
                WHERE LOWER(title) = ? AND LOWER(genre) = ? AND LOWER(duration) = ? AND LOWER(description) = ? AND LOWER(rating) = ? AND movie_id != ? AND active = 1',
                [
                    strtolower($request->title), 
                    strtolower($request->genre), 
                    strtolower($request->duration),
                    strtolower($request->description),
                    strtolower($request->rating),
                    $id
                ]
            );

            if (!empty($existingMovie)) {
                return response()->json([
                    'message' => 'Another movie with the same details already exists.'
                ], 422);
            }

            // Check if another movie has the same title (excluding current movie)
            $existingMovieTitle = DB::select('
                SELECT * FROM movies 
                WHERE LOWER(title) = ? AND movie_id != ? AND active = 1',
                [
                    strtolower($request->title), 
                    $id
                ]
            );

            if (!empty($existingMovieTitle)) {
                return response()->json([
                    'message' => 'Another movie with the same title already exists.'
                ], 422);
            }

            // Update the movie
            DB::update('
                UPDATE movies 
                SET title = ?, genre = ?, duration = ?, description = ?, rating = ?, updated_at = NOW() 
                WHERE movie_id = ?',
                [
                    $request->title,
                    $request->genre,
                    $request->duration,
                    $request->description,
                    $request->rating,
                    $id
                ]
            );

            return response()->json([
                'success' => true,
                'message' => 'Movie updated successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update movie: ' . $e->getMessage()
            ], 500);
        }
    }
}
