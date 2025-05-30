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

    public function dataTables(Request $request)
    {
        $filter = $request->input('filter', '');
        
        $query = '
            SELECT 
                c.*,
                m.name as mall_name,
                CONCAT(mg.first_name, " ", mg.last_name) as manager_name
            FROM cinemas c
            JOIN malls m ON c.mall_id = m.mall_id
            JOIN managers mg ON c.manager_id = mg.manager_id
        ';

        // Apply filter
        if ($filter === 'active') {
            $query .= ' WHERE c.active = 1';
        } elseif ($filter === 'inactive') {
            $query .= ' WHERE c.active = 0';
        }

        $cinemas = DB::select($query);
        return DataTables::of($cinemas)
            ->addColumn('action', function($row) {
                $buttons = '';
                
                if ($row->active == 1) {
                    $buttons .= '<i class="fas fa-edit edit-cinema" data-id="'.$row->cinema_id.'"></i>';
                    $buttons .= '<i class="fas fa-trash delete-cinema" data-id="'.$row->cinema_id.'"></i>';
                } else {
                    $buttons .= '<i class="fas fa-undo restore-cinema" data-id="'.$row->cinema_id.'"></i>';
                }
                
                return $buttons;
            })
            ->rawColumns(['action'])
            ->make(true);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'mall_id' => 'required|exists:malls,mall_id',
            'manager_id' => 'required|exists:managers,manager_id',
        ]);

        // Check if cinema with same name exists in the same mall
        $existingCinema = DB::select('
            SELECT * FROM cinemas 
            WHERE LOWER(name) = ? AND mall_id = ?', 
            [strtolower($request->name), $request->mall_id]
        );

        if (!empty($existingCinema)) {
            return response()->json([
                'message' => 'A cinema with the same name already exists in this mall.'
            ], 422);
        }

        // Insert new cinema
        DB::insert('INSERT INTO cinemas (name, mall_id, manager_id, created_at, updated_at) VALUES (?, ?, ?, NOW(), NOW())', [
            $request->name,
            $request->mall_id,
            $request->manager_id,
        ]);

        return response()->json(['message' => 'Cinema added successfully']);
    }

    public function update($id, Request $request)
    {
        try {
            $request->validate([
                'name' => 'required|string|max:255',
                'mall_id' => 'required|exists:malls,mall_id',
                'manager_id' => 'required|exists:managers,manager_id',
            ]);

            // Get current cinema
            $currentCinema = DB::select('SELECT * FROM cinemas WHERE cinema_id = ?', [$id]);
            if (empty($currentCinema)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cinema not found'
                ], 404);
            }

            $currentCinema = $currentCinema[0];

            // Check if any data is actually changing
            if ($currentCinema->name === $request->name && 
                $currentCinema->mall_id == $request->mall_id && 
                $currentCinema->manager_id == $request->manager_id) {
                return response()->json([
                    'success' => true,
                    'message' => 'No changes were made to the cinema.'
                ]);
            }

            // Check if cinema name exists in the same mall (excluding current cinema)
            if ($currentCinema->name !== $request->name || $currentCinema->mall_id != $request->mall_id) {
                $existingCinema = DB::select('
                    SELECT * FROM cinemas 
                    WHERE LOWER(name) = ? 
                    AND mall_id = ? 
                    AND cinema_id != ?',
                    [
                        strtolower($request->name),
                        $request->mall_id,
                        $id
                    ]
                );

                if (!empty($existingCinema)) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Another cinema with the same name already exists in this mall.'
                    ], 422);
                }
            }

            // Update the cinema
            DB::update('
                UPDATE cinemas 
                SET name = ?, 
                    mall_id = ?, 
                    manager_id = ?, 
                    updated_at = NOW() 
                WHERE cinema_id = ?',
                [
                    $request->name,
                    $request->mall_id,
                    $request->manager_id,
                    $id
                ]
            );

            return response()->json([
                'success' => true,
                'message' => 'Cinema updated successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update cinema: ' . $e->getMessage()
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
                    'message' => 'Cannot deactivate cinema: There are still active screenings'
                ], 422);
            }

            // Update the active status to 0
            DB::update('UPDATE cinemas SET active = 0, updated_at = NOW() WHERE cinema_id = ?', [$id]);

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

    public function restore($id)
    {
        try {
            $cinema = DB::select('SELECT * FROM cinemas WHERE cinema_id = ?', [$id]);
            
            if (empty($cinema)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cinema not found'
                ], 404);
            }

            // Update the active status to 1
            DB::update('UPDATE cinemas SET active = 1, updated_at = NOW() WHERE cinema_id = ?', [$id]);

            return response()->json([
                'success' => true,
                'message' => 'Cinema has been restored successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to restore cinema: ' . $e->getMessage()
            ], 500);
        }
    }
}
