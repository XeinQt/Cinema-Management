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
        $cinemas = DB::select('
            SELECT 
                c.*,
                m.name as mall_name,
                CONCAT(mg.first_name, " ", mg.last_name) as manager_full_name
            FROM cinemas c
            JOIN malls m ON c.mall_id = m.mall_id
            JOIN managers mg ON c.manager_id = mg.manager_id
            WHERE c.active = 1
        ');
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

    public function update($id, Request $request)
    {
        try {
            $request->validate([
                'mall_id' => 'required|integer',
                'manager_id' => 'required|integer',
                'cinema_name' => 'required|string|max:255',
            ]);

            return DB::transaction(function () use ($request, $id) {
                // Check if cinema exists
                $cinema = DB::select('SELECT * FROM cinemas WHERE cinema_id = ? AND active = 1', [$id]);
                if (empty($cinema)) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Cinema not found or is inactive',
                    ], 404);
                }

                // Check if mall exists and is active
                $mall = DB::select('SELECT mall_id FROM malls WHERE mall_id = ? AND active = 1 LIMIT 1', [$request->mall_id]);
                if (empty($mall)) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Mall not found or is inactive',
                    ], 404);
                }

                // Check if manager exists and is active
                $manager = DB::select('SELECT manager_id FROM managers WHERE manager_id = ? AND active = 1 LIMIT 1', [$request->manager_id]);
                if (empty($manager)) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Manager not found or is inactive',
                    ], 404);
                }

                // Check if cinema name exists for other cinemas
                $existingCinema = DB::select('SELECT * FROM cinemas WHERE name = ? AND cinema_id != ? AND active = 1', [$request->cinema_name, $id]);
                if (!empty($existingCinema)) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Cinema name already exists.',
                    ], 409);
                }

                // Update the cinema
                DB::update('UPDATE cinemas SET mall_id = ?, manager_id = ?, name = ?, updated_at = NOW() WHERE cinema_id = ?', [
                    $request->mall_id,
                    $request->manager_id,
                    $request->cinema_name,
                    $id
                ]);

                return response()->json([
                    'success' => true,
                    'message' => 'Cinema updated successfully',
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
                'message' => 'Failed to update cinema: ' . $e->getMessage(),
            ], 500);
        }
    }
}
