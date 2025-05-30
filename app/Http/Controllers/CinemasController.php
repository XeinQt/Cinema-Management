<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\DataTables;

class CinemasController extends Controller
{
    public function list()
    {
        return view('cinemas.list');
    }

    public function dataTables()
    {
        $cinemas = DB::select('SELECT * FROM cinemas WHERE active = 1');
        return DataTables::of($cinemas)->make(true);
    }

    public function store(Request $request)
    {
        try {
            $request->validate([
                'name' => 'required|string|max:255',
                'manager_full_name' => 'required|string|max:255',
                'cinema_name' => 'required|string|max:255',
            ]);

            return DB::transaction(function () use ($request) {
                $mall = DB::select('SELECT mall_id FROM malls WHERE name = ? AND active = 1 LIMIT 1', [$request->name]);

                if (empty($mall)) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Mall not found or is inactive',
                    ], 404);
                }

                $mallId = $mall[0]->mall_id;

                $fullName = trim($request->manager_full_name);
                $nameParts = preg_split('/\s+/', $fullName);

                if (count($nameParts) < 2) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Please provide both first and last name for the manager.',
                    ], 422);
                }

                $firstName = $nameParts[0];
                $lastName = $nameParts[1];

                $manager = DB::select('SELECT manager_id FROM managers WHERE first_name = ? AND last_name = ? AND active = 1 LIMIT 1', [$firstName, $lastName]);

                if (empty($manager)) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Manager not found or is inactive',
                    ], 404);
                }

                $managerId = $manager[0]->manager_id;

                $existingCinema = DB::select('SELECT * FROM cinemas WHERE name = ? AND active = 1', [$request->cinema_name]);
                if (!empty($existingCinema)) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Cinema name already exists.',
                    ], 409);
                }

                DB::insert('INSERT INTO cinemas (mall_id, manager_id, name, active, created_at, updated_at) VALUES (?, ?, ?, 1, NOW(), NOW())', [
                    $mallId,
                    $managerId,
                    $request->cinema_name,
                ]);

                return response()->json([
                    'success' => true,
                    'message' => 'Cinema created successfully',
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
                'message' => 'Failed to create cinema: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function updateStatus($id)
    {
        try {
            // Check if cinema exists
            $cinema = DB::select('SELECT * FROM cinemas WHERE cinema_id = ?', [$id]);
            
            if (empty($cinema)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cinema not found'
                ], 404);
            }

            // Check if cinema has any active screenings
            $activeScreenings = DB::select('SELECT * FROM screenings WHERE cinema_id = ? AND active = 1', [$id]);
            if (!empty($activeScreenings)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot deactivate cinema: It has active screenings scheduled'
                ], 422);
            }

            // Update the active status to 0
            DB::update('UPDATE cinemas SET active = 0 WHERE cinema_id = ?', [$id]);

            return response()->json([
                'success' => true,
                'message' => 'Cinema has been deactivated successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to deactivate cinema: ' . $e->getMessage()
            ], 500);
        }
    }
}
