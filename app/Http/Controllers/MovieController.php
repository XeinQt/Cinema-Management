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
        try {
            $filter = $request->input('filter', '');
            
            $query = DB::table('movies')
                ->select([
                    'movie_id',
                    'title',
                    'genre',
                    'duration',
                    'description',
                    'rating',
                    'active'
                ]);
            
            // Apply filter
            if ($filter === 'active') {
                $query->where('active', 1);
            } elseif ($filter === 'inactive') {
                $query->where('active', 0);
            }
            
            return DataTables::of($query)
                ->addColumn('action', function($row) {
                    $buttons = '';
                    
                    if ($row->active == 1) {
                        $buttons .= '<i class="fas fa-edit edit-movie" data-id="'.$row->movie_id.'"></i>';
                        $buttons .= '<i class="fas fa-trash delete-movie" data-id="'.$row->movie_id.'"></i>';
                    } else {
                        $buttons .= '<i class="fas fa-undo restore-movie" data-id="'.$row->movie_id.'"></i>';
                    }
                    
                    return $buttons;
                })
                ->rawColumns(['action'])
                ->make(true);
        } catch (\Exception $e) {
            \Log::error('DataTables Error: ' . $e->getMessage());
            return response()->json([
                'error' => 'Failed to load movie data: ' . $e->getMessage()
            ], 500);
        }
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'genre' => 'required|string|max:255',
            'duration' => 'required|string|max:255',
            'description' => 'required|string',
            'rating' => 'required|string|max:10',
        ]);

        // Check if movie with same title exists (case-insensitive)
        $existingMovie = DB::select('
            SELECT * FROM movies 
            WHERE LOWER(title) = ?', 
            [strtolower($request->title)]
        );

        if (!empty($existingMovie)) {
            return response()->json([
                'message' => 'A movie with the same title already exists.'
            ], 422);
        }

        // Insert new movie
        DB::insert('INSERT INTO movies (title, genre, duration, description, rating, created_at, updated_at) VALUES (?, ?, ?, ?, ?, NOW(), NOW())', [
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

            // Check if movie is being used in any active screening
            $activeScreenings = DB::select('SELECT * FROM screenings WHERE movie_id = ? AND active = 1', [$id]);
            if (!empty($activeScreenings)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot deactivate movie: It is still being used in active screenings'
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

    public function update($id, Request $request)
    {
        try {
            $request->validate([
                'title' => 'required|string|max:255',
                'genre' => 'required|string|max:255',
                'duration' => 'required|string|max:255',
                'description' => 'required|string',
                'rating' => 'required|string|max:10',
            ]);

            // Get current movie
            $currentMovie = DB::select('SELECT * FROM movies WHERE movie_id = ?', [$id]);
            if (empty($currentMovie)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Movie not found'
                ], 404);
            }

            $currentMovie = $currentMovie[0];

            // Check if any data is actually changing
            if ($currentMovie->title === $request->title && 
                $currentMovie->genre === $request->genre && 
                $currentMovie->duration == $request->duration &&
                $currentMovie->description === $request->description &&
                $currentMovie->rating === $request->rating) {
                return response()->json([
                    'success' => true,
                    'message' => 'No changes were made to the movie.'
                ]);
            }

            // Check if title is being changed and if it would conflict with existing titles
            if (strtolower($currentMovie->title) !== strtolower($request->title)) {
                $existingMovieTitle = DB::select('
                    SELECT * FROM movies 
                    WHERE LOWER(title) = ? 
                    AND movie_id != ?',
                    [
                        strtolower($request->title),
                        $id
                    ]
                );

                if (!empty($existingMovieTitle)) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Another movie with the same title already exists.'
                    ], 422);
                }
            }

            // Update the movie
            DB::update('
                UPDATE movies 
                SET title = ?, 
                    genre = ?, 
                    duration = ?,
                    description = ?,
                    rating = ?,
                    updated_at = NOW() 
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

    public function restore($id)
    {
        try {
            $movie = DB::select('SELECT * FROM movies WHERE movie_id = ?', [$id]);
            
            if (empty($movie)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Movie not found'
                ], 404);
            }

            // Update the active status to 1
            DB::update('UPDATE movies SET active = 1, updated_at = NOW() WHERE movie_id = ?', [$id]);

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
}
