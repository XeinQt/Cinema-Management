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
        return DataTables::of($malls)
            ->addColumn('action', function($row) {
                $buttons = '';
                if ($row->active == 1) {
                    $buttons .= '<i class="fas fa-edit edit-mall" data-id="'.$row->mall_id.'"></i>';
                    $buttons .= '<i class="fas fa-trash delete-mall" data-id="'.$row->mall_id.'"></i>';
                } else {
                    $buttons .= '<i class="fas fa-undo restore-mall" data-id="'.$row->mall_id.'"></i>';
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
            // Get current mall with its associated cinemas
            $mall = DB::select('
                SELECT m.*, COUNT(c.cinema_id) as active_cinemas 
                FROM malls m 
                LEFT JOIN cinemas c ON m.mall_id = c.mall_id AND c.active = 1
                WHERE m.mall_id = ?
                GROUP BY m.mall_id, m.name, m.location, m.description, m.active, m.created_at, m.updated_at', 
                [$id]
            );
            
            if (empty($mall)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Mall not found'
                ], 404);
            }

            if ($mall[0]->active_cinemas > 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot deactivate mall: It has ' . $mall[0]->active_cinemas . ' active cinema(s)'
                ], 422);
            }

            // Update the active status to 0
            DB::update('UPDATE malls SET active = 0, updated_at = NOW() WHERE mall_id = ?', [$id]);

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

            // Get current mall
            $currentMall = DB::select('SELECT * FROM malls WHERE mall_id = ?', [$id]);
            if (empty($currentMall)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Mall not found'
                ], 404);
            }

            $currentMall = $currentMall[0];

            // Check if any data is actually changing
            if ($currentMall->name === $request->name && 
                $currentMall->location === $request->location && 
                $currentMall->description === $request->description) {
                return response()->json([
                    'success' => true,
                    'message' => 'No changes were made to the mall.'
                ]);
            }

            // Only check for duplicates if the data is actually changing
            if ($currentMall->name !== $request->name || 
                $currentMall->location !== $request->location || 
                $currentMall->description !== $request->description) {

                // Check if another mall has the same details (excluding current mall)
                $existingMall = DB::select('
                    SELECT * FROM malls 
                    WHERE LOWER(name) = ? 
                    AND LOWER(location) = ? 
                    AND LOWER(description) = ? 
                    AND mall_id != ?',
                    [
                        strtolower($request->name), 
                        strtolower($request->location), 
                        strtolower($request->description),
                        $id
                    ]
                );

                if (!empty($existingMall)) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Another mall with the same details already exists.'
                    ], 422);
                }
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

    public function index(Request $request){

        
    }
}

