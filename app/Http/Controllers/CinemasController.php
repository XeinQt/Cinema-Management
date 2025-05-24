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
        $cinemas = DB::select('SELECT * FROM cinemas');
        return DataTables::of($cinemas)->make(true);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'manager_full_name' => 'required|string|max:255',
            'cinema_name' => 'required|string|max:255',
        ]);

        $mall = DB::select('SELECT mall_id FROM malls WHERE name = ? LIMIT 1', [$request->name]);

        if (empty($mall)) {
            return response()->json([
                'success' => false,
                'message' => 'Mall not found',
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

        $manager = DB::select('SELECT manager_id FROM managers WHERE first_name = ? AND last_name = ? LIMIT 1', [$firstName, $lastName]);

        if (empty($manager)) {
            return response()->json([
                'success' => false,
                'message' => 'Manager not found',
            ], 404);
        }

        $managerId = $manager[0]->manager_id;

        $existingCinema = DB::select('SELECT * FROM cinemas WHERE name = ?', [$request->cinema_name]);
        if (!empty($existingCinema)) {
            return response()->json([
                'success' => false,
                'message' => 'Cinema name already exists.',
            ], 409);
        }

        DB::insert('INSERT INTO cinemas (mall_id, manager_id, name, created_at, updated_at) VALUES (?, ?, ?, NOW(), NOW())', [
            $mallId,
            $managerId,
            $request->cinema_name,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Cinema created successfully',
        ]);
    }
}
