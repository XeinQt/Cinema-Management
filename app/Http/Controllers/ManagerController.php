<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\DataTables;
use Illuminate\Http\Request;

class ManagerController extends Controller
{
    public function list () {
       
        return view('manager.list');
    }
     public function dataTables(Request $request)
    {
        $filter = $request->input('filter', '');
        
        $query = 'SELECT * FROM managers';
        
        // Apply filter
        if ($filter === 'active') {
            $query .= ' WHERE active = 1';
        } elseif ($filter === 'inactive') {
            $query .= ' WHERE active = 0';
        }
        
        $query .= ' ORDER BY manager_id ASC';
        
        $managers = DB::select($query);
        return DataTables::of($managers)
            ->addColumn('action', function($row) {
                $buttons = '';
                
                if ($row->active == 1) {
                     $buttons .= '<i class="fas fa-edit edit-manager" data-id="'.$row->manager_id.'"></i>';
                    $buttons .= '<i class="fas fa-trash delete-manager" data-id="'.$row->manager_id.'"></i>';
                } else {
                    $buttons .= '<i class="fas fa-undo restore-manager" data-id="'.$row->manager_id.'"></i>';
                }
                
                return $buttons;
            })
            ->rawColumns(['action'])
            ->make(true);
    }

    public function store(Request $request)
    {
        $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|string|max:255',
             'phonenumber' => 'required|string|max:255',
        ]);

         $existingManagers = DB::table('managers')
            ->whereRaw('LOWER(email) = ?', [strtolower($request->email)])
            ->exists();

        if ($existingManagers) {
            return response()->json([
                'message' => 'Email already exists.'
            ], 422);
        }


        // Check if mall with same fnamae, lname, email  and phono number exists (case-insensitive)
        $existingManagerFnameAndLname = DB::select('
            SELECT * FROM managers 
            WHERE LOWER(first_name) = ? AND LOWER(last_name) = ?', 
            [strtolower($request->first_name), strtolower($request->last_name)]
        );

        if (!empty($existingManagerFnameAndLname)) {
            return response()->json([
                'message' => 'A Manager with the same firstname, lastname already exists.'
            ], 422);
        }
        // Check if mall with same fnamae, lname, email  and phono number exists (case-insensitive)
        $existingManager = DB::select('
            SELECT * FROM managers 
            WHERE LOWER(first_name) = ? AND LOWER(last_name) = ? AND LOWER(email) = ? AND LOWER(phonenumber) = ?', 
            [strtolower($request->first_name), strtolower($request->last_name), strtolower($request->email), strtolower($request->phonenumber)]
        );

        if (!empty($existingManager)) {
            return response()->json([
                'message' => 'A Manager with the same firstname, lastname, email and Phone number already exists.'
            ], 422);
        }

        // Insert new mall
        DB::insert('INSERT INTO managers (first_name, last_name, email, phonenumber , created_at, updated_at) VALUES (?, ?, ?, ?, NOW(), NOW())', [
            $request->first_name,
            $request->last_name,
            $request->email,
            $request->phonenumber,
        ]);

        return response()->json(['message' => 'Manager added successfully']);
    }


    public function updateStatus($id)
    {
        try {
            // Check if manager exists
            $manager = DB::select('SELECT * FROM managers WHERE manager_id = ?', [$id]);
            
            if (empty($manager)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Manager not found'
                ], 404);
            }

            // Check if manager is assigned to any active cinemas
            $activeCinemas = DB::select('SELECT * FROM cinemas WHERE manager_id = ? AND active = 1', [$id]);
            if (!empty($activeCinemas)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot deactivate manager: They are still assigned to active cinemas'
                ], 422);
            }

            // Update the active status to 0
            DB::update('UPDATE managers SET active = 0 WHERE manager_id = ?', [$id]);

            return response()->json([
                'success' => true,
                'message' => 'Manager has been deactivated successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to deactivate manager: ' . $e->getMessage()
            ], 500);
        }
    }

    public function update($id, Request $request)
    {
        try {
            $request->validate([
                'first_name' => 'required|string|max:255',
                'last_name' => 'required|string|max:255',
                'email' => 'required|string|max:255|email',
                'phonenumber' => 'required|string|max:255',
            ]);

            // Get current manager
            $currentManager = DB::select('SELECT * FROM managers WHERE manager_id = ?', [$id]);
            if (empty($currentManager)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Manager not found'
                ], 404);
            }

            $currentManager = $currentManager[0];

            // Check if any data is actually changing
            if ($currentManager->first_name === $request->first_name && 
                $currentManager->last_name === $request->last_name && 
                $currentManager->email === $request->email && 
                $currentManager->phonenumber === $request->phonenumber) {
                return response()->json([
                    'success' => true,
                    'message' => 'No changes were made to the manager.'
                ]);
            }

            // Check if email is being changed
            if (strtolower($currentManager->email) !== strtolower($request->email)) {
                $existingManagerEmail = DB::select('
                    SELECT * FROM managers 
                    WHERE LOWER(email) = ? 
                    AND manager_id != ?',
                    [
                        strtolower($request->email),
                        $id
                    ]
                );

                if (!empty($existingManagerEmail)) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Another manager with the same email already exists.'
                    ], 422);
                }
            }

            // Check if name combination is being changed
            if (strtolower($currentManager->first_name) !== strtolower($request->first_name) || 
                strtolower($currentManager->last_name) !== strtolower($request->last_name)) {
                
                $existingManagerName = DB::select('
                    SELECT * FROM managers 
                    WHERE LOWER(first_name) = ? 
                    AND LOWER(last_name) = ? 
                    AND manager_id != ?',
                    [
                        strtolower($request->first_name),
                        strtolower($request->last_name),
                        $id
                    ]
                );

                if (!empty($existingManagerName)) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Another manager with the same first name and last name already exists.'
                    ], 422);
                }
            }

            // Update the manager
            DB::update('
                UPDATE managers 
                SET first_name = ?, 
                    last_name = ?, 
                    email = ?, 
                    phonenumber = ?, 
                    updated_at = NOW() 
                WHERE manager_id = ?',
                [
                    $request->first_name,
                    $request->last_name,
                    $request->email,
                    $request->phonenumber,
                    $id
                ]
            );

            return response()->json([
                'success' => true,
                'message' => 'Manager updated successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update manager: ' . $e->getMessage()
            ], 500);
        }
    }

    public function restore($id)
    {
        try {
            $manager = DB::select('SELECT * FROM managers WHERE manager_id = ?', [$id]);
            
            if (empty($manager)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Manager not found'
                ], 404);
            }

            // Update the active status to 1
            DB::update('UPDATE managers SET active = 1, updated_at = NOW() WHERE manager_id = ?', [$id]);

            return response()->json([
                'success' => true,
                'message' => 'Manager has been restored successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to restore manager: ' . $e->getMessage()
            ], 500);
        }
    }
}
