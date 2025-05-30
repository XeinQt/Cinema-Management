<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\DataTables;

class ScreeningController extends Controller
{
    public function list() {
        return view ('screening.list');
    }

    public function dataTables()
    {
        $screenings = DB::select('
            SELECT 
                s.*,
                c.name as cinema_name,
                m.title as movie_title
            FROM screenings s
            JOIN cinemas c ON s.cinema_id = c.cinema_id
            JOIN movies m ON s.movie_id = m.movie_id
            WHERE s.active = 1
        ');
        return DataTables::of($screenings)->make(true);
    }

    public function store(Request $request)
    {
        $request->validate([
            'cinemaName' => 'required|string|max:255',
            'movieName' => 'required|string|max:255',
            'time' => 'required|date', // better validation for datetime
        ]);

        $cinema = DB::select('SELECT cinema_id FROM cinemas WHERE name = ? LIMIT 1', [$request->cinemaName]);

        if (empty($cinema)) {
            return response()->json([
                'success' => false,
                'message' => 'Cinema not found',
            ], 404);
        }
        $cinemaId = $cinema[0]->cinema_id;

        $movie = DB::select('SELECT movie_id FROM movies WHERE title = ? LIMIT 1', [$request->movieName]);

        if (empty($movie)) {
            return response()->json([
                'success' => false,
                'message' => 'Movie not found',
            ], 404);
        }
        $movieId = $movie[0]->movie_id;

        $time = \Carbon\Carbon::parse($request->time)->format('Y-m-d H:i:s');

        $existingScreening = DB::select('
            SELECT * FROM screenings 
            WHERE cinema_id = ? AND movie_id = ? AND screening_time = ?', 
            [$cinemaId, $movieId, $time]
        );

        if (!empty($existingScreening)) {
            return response()->json([
                'success' => false,
                'message' => 'This screening already exists.',
            ], 409);
        }

        DB::insert('INSERT INTO screenings (cinema_id, movie_id, screening_time, active, created_at, updated_at) VALUES (?, ?, ?, ?, NOW(), NOW())', [
            $cinemaId,
            $movieId,
            $request->time,
            1,  // <-- comma here is required in SQL string before NOW()
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Screening created successfully',
        ]);
    }


    public function updateStatus($id)
    {
        try {
            $screening = DB::select('SELECT * FROM screenings WHERE screening_id = ?', [$id]);
            
            if (empty($screening)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Screening not found'
                ], 404);
            }

            // Check if screening has any active bookings
            $activeBookings = DB::select('SELECT * FROM booking WHERE screening_id = ? AND active = 1', [$id]);
            if (!empty($activeBookings)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot deactivate screening: It has active bookings'
                ], 422);
            }

            // Update the active status to 0
            DB::update('UPDATE screenings SET active = 0 WHERE screening_id = ?', [$id]);

            return response()->json([
                'success' => true,
                'message' => 'Screening has been deactivated successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to deactivate screening: ' . $e->getMessage()
            ], 500);
        }
    }

    public function update($id, Request $request)
    {
        try {
            $request->validate([
                'cinemaName' => 'required|string|max:255',
                'movieName' => 'required|string|max:255',
                'time' => 'required|date',
            ]);

            return DB::transaction(function () use ($request, $id) {
                // Check if screening exists
                $screening = DB::select('SELECT * FROM screenings WHERE screening_id = ? AND active = 1', [$id]);
                if (empty($screening)) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Screening not found or is inactive',
                    ], 404);
                }

                // Get cinema ID
                $cinema = DB::select('SELECT cinema_id FROM cinemas WHERE name = ? AND active = 1 LIMIT 1', [$request->cinemaName]);
                if (empty($cinema)) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Cinema not found or is inactive',
                    ], 404);
                }
                $cinemaId = $cinema[0]->cinema_id;

                // Get movie ID
                $movie = DB::select('SELECT movie_id FROM movies WHERE title = ? AND active = 1 LIMIT 1', [$request->movieName]);
                if (empty($movie)) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Movie not found or is inactive',
                    ], 404);
                }
                $movieId = $movie[0]->movie_id;

                // Format time
                $time = \Carbon\Carbon::parse($request->time)->format('Y-m-d H:i:s');

                // Check if screening time exists for other screenings
                $existingScreening = DB::select('
                    SELECT * FROM screenings 
                    WHERE cinema_id = ? AND movie_id = ? AND screening_time = ? AND screening_id != ? AND active = 1', 
                    [$cinemaId, $movieId, $time, $id]
                );

                if (!empty($existingScreening)) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Another screening with the same details already exists.',
                    ], 409);
                }

                // Update the screening
                DB::update('
                    UPDATE screenings 
                    SET cinema_id = ?, movie_id = ?, screening_time = ?, updated_at = NOW() 
                    WHERE screening_id = ?',
                    [
                        $cinemaId,
                        $movieId,
                        $time,
                        $id
                    ]
                );

                return response()->json([
                    'success' => true,
                    'message' => 'Screening updated successfully',
                ]);
            });
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update screening: ' . $e->getMessage(),
            ], 500);
        }
    }

}
