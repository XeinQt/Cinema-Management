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

    public function dataTables(Request $request)
    {
        $filter = $request->input('filter', '');
        
        $query = DB::table('screenings as s')
            ->join('cinemas as c', 's.cinema_id', '=', 'c.cinema_id')
            ->join('movies as m', 's.movie_id', '=', 'm.movie_id')
            ->select([
                's.screening_id',
                's.screening_time',
                's.active',
                's.cinema_id',
                's.movie_id',
                'c.name as cinema_name',
                'm.title as movie_title'
            ]);

        // Apply filter
        if ($filter === 'active') {
            $query->where('s.active', 1);
        } elseif ($filter === 'inactive') {
            $query->where('s.active', 0);
        }

        return DataTables::of($query)
            ->addColumn('action', function($row) {
                $buttons = '';
                
                if ($row->active == 1) {
                    $buttons .= '<i class="fas fa-edit edit-screening" data-id="'.$row->screening_id.'"></i>';
                    $buttons .= '<i class="fas fa-trash delete-screening" data-id="'.$row->screening_id.'"></i>';
                } else {
                    $buttons .= '<i class="fas fa-undo restore-screening" data-id="'.$row->screening_id.'"></i>';
                }
                
                return $buttons;
            })
            ->rawColumns(['action'])
            ->make(true);
    }

    public function store(Request $request)
    {
        $request->validate([
            'cinema_select' => 'required|numeric',
            'movie_select' => 'required|numeric',
            'time' => 'required|date',
        ]);

        // Check if cinema exists and is active
        $cinema = DB::select('SELECT cinema_id FROM cinemas WHERE cinema_id = ? AND active = 1 LIMIT 1', [$request->cinema_select]);
        if (empty($cinema)) {
            return response()->json([
                'success' => false,
                'message' => 'Cinema not found or is inactive',
            ], 404);
        }

        // Check if movie exists and is active
        $movie = DB::select('SELECT movie_id FROM movies WHERE movie_id = ? AND active = 1 LIMIT 1', [$request->movie_select]);
        if (empty($movie)) {
            return response()->json([
                'success' => false,
                'message' => 'Movie not found or is inactive',
            ], 404);
        }

        $time = \Carbon\Carbon::parse($request->time)->format('Y-m-d H:i:s');

        $existingScreening = DB::select('
            SELECT * FROM screenings 
            WHERE cinema_id = ? AND movie_id = ? AND screening_time = ? AND active = 1', 
            [$request->cinema_select, $request->movie_select, $time]
        );

        if (!empty($existingScreening)) {
            return response()->json([
                'success' => false,
                'message' => 'This screening already exists.',
            ], 409);
        }

        DB::insert('INSERT INTO screenings (cinema_id, movie_id, screening_time, active, created_at, updated_at) VALUES (?, ?, ?, ?, NOW(), NOW())', [
            $request->cinema_select,
            $request->movie_select,
            $time,
            1,
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
                'cinema_select' => 'required|numeric',
                'movie_select' => 'required|numeric',
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

                // Format time for comparison
                $time = \Carbon\Carbon::parse($request->time)->format('Y-m-d H:i:s');

                // Check if any data is actually changing
                if ($screening[0]->cinema_id == $request->cinema_select && 
                    $screening[0]->movie_id == $request->movie_select && 
                    $screening[0]->screening_time == $time) {
                    return response()->json([
                        'success' => true,
                        'message' => 'No changes were made to the screening.'
                    ]);
                }

                // Check if cinema exists and is active
                $cinema = DB::select('SELECT cinema_id FROM cinemas WHERE cinema_id = ? AND active = 1 LIMIT 1', [$request->cinema_select]);
                if (empty($cinema)) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Cinema not found or is inactive',
                    ], 404);
                }

                // Check if movie exists and is active
                $movie = DB::select('SELECT movie_id FROM movies WHERE movie_id = ? AND active = 1 LIMIT 1', [$request->movie_select]);
                if (empty($movie)) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Movie not found or is inactive',
                    ], 404);
                }

                // Check if screening time exists for other screenings
                $existingScreening = DB::select('
                    SELECT * FROM screenings 
                    WHERE cinema_id = ? AND movie_id = ? AND screening_time = ? AND screening_id != ? AND active = 1', 
                    [$request->cinema_select, $request->movie_select, $time, $id]
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
                        $request->cinema_select,
                        $request->movie_select,
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

    public function restore($id)
    {
        try {
            // Check if screening exists
            $screening = DB::select('SELECT * FROM screenings WHERE screening_id = ?', [$id]);
            
            if (empty($screening)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Screening not found'
                ], 404);
            }

            // Update the active status to 1
            DB::update('UPDATE screenings SET active = 1 WHERE screening_id = ?', [$id]);

            return response()->json([
                'success' => true,
                'message' => 'Screening has been restored successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to restore screening: ' . $e->getMessage()
            ], 500);
        }
    }

    public function getActiveCinemas()
    {
        try {
            $cinemas = DB::table('cinemas')
                ->where('active', 1)
                ->select('cinema_id', 'name')
                ->orderBy('name')
                ->get();

            return response()->json($cinemas);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch cinemas: ' . $e->getMessage()
            ], 500);
        }
    }

    public function getActiveMovies()
    {
        try {
            $movies = DB::table('movies')
                ->where('active', 1)
                ->select('movie_id', 'title')
                ->orderBy('title')
                ->get();

            return response()->json($movies);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch movies: ' . $e->getMessage()
            ], 500);
        }
    }
}
