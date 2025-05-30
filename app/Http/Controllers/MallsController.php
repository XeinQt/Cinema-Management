<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Contracts\DataTable;
use Yajra\DataTables\DataTables;

class MallsController extends Controller
{
    public function list() 
    {
        return view('malls.list');
    }

    public function dataTables(Request $request)
    {
        $filter = $request->input('filter', '');
        
        $query = 'SELECT * FROM malls';
        
        // Apply filter
        if ($filter === 'active') {
            $query .= ' WHERE active = 1';
        } elseif ($filter === 'inactive') {
            $query .= ' WHERE active = 0';
        }
        
        $query .= ' ORDER BY mall_id DESC';
        
        $malls = DB::select($query);
        return DataTables::of($malls)->make(true);
    }

   public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'location' => 'required|string|max:255',
            'description' => 'required|string|max:255',
        ]);

        $existingMall = DB::select('
            SELECT * FROM malls 
            WHERE LOWER(name) = ? AND LOWER(location) = ? AND LOWER(description) = ?',
            [strtolower($request->name), strtolower($request->location), strtolower($request->description)]
        );

        if (!empty($existingMall)) {
            return response()->json([
                'message' => 'A mall with the same name, location, and description already exists.'
            ], 422);
        }

        DB::insert('INSERT INTO malls (name, location, description, active, created_at, updated_at) VALUES (?, ?, ?, 1, NOW(), NOW())', [
            $request->name,
            $request->location,
            $request->description,
        ]);

        return response()->json(['message' => 'Mall added successfully']);
    }

    public function updateStatus($id)
    {
        try {
            $mall = DB::select('SELECT * FROM malls WHERE mall_id = ?', [$id]);
            
            if (empty($mall)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Mall not found'
                ], 404);
            }

            // Check if mall has any active cinemas
            $activeCinemas = DB::select('SELECT * FROM cinemas WHERE mall_id = ? AND active = 1', [$id]);
            if (!empty($activeCinemas)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot deactivate mall: It has active cinemas'
                ], 422);
            }

            // Update the active status to 0
            DB::update('UPDATE malls SET active = 0 WHERE mall_id = ?', [$id]);

            return response()->json([
                'success' => true,
                'message' => 'Mall has been deactivated successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to deactivate mall: ' . $e->getMessage()
            ], 500);
        }
    }

    public function update($id, Request $request)
    {
        try {
            $request->validate([
                'name' => 'required|string|max:255',
                'location' => 'required|string|max:255',
                'description' => 'required|string|max:255',
            ]);

            // Check if mall exists
            $mall = DB::select('SELECT * FROM malls WHERE mall_id = ?', [$id]);
            if (empty($mall)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Mall not found'
                ], 404);
            }

            // Check if another mall has the same details (excluding current mall)
            $existingMall = DB::select('
                SELECT * FROM malls 
                WHERE LOWER(name) = ? AND LOWER(location) = ? AND LOWER(description) = ? AND mall_id != ?',
                [
                    strtolower($request->name), 
                    strtolower($request->location), 
                    strtolower($request->description),
                    $id
                ]
            );

            if (!empty($existingMall)) {
                return response()->json([
                    'message' => 'Another mall with the same details already exists.'
                ], 422);
            }

            // Update the mall
            DB::update('
                UPDATE malls 
                SET name = ?, location = ?, description = ?, updated_at = NOW() 
                WHERE mall_id = ?',
                [
                    $request->name,
                    $request->location,
                    $request->description,
                    $id
                ]
            );

            return response()->json([
                'success' => true,
                'message' => 'Mall updated successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update mall: ' . $e->getMessage()
            ], 500);
        }
    }

    public function restore($id)
    {
        try {
            $mall = DB::select('SELECT * FROM malls WHERE mall_id = ?', [$id]);
            
            if (empty($mall)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Mall not found'
                ], 404);
            }

            // Update the active status to 1
            DB::update('UPDATE malls SET active = 1, updated_at = NOW() WHERE mall_id = ?', [$id]);

            return response()->json([
                'success' => true,
                'message' => 'Mall has been restored successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to restore mall: ' . $e->getMessage()
            ], 500);
        }
    }
}

