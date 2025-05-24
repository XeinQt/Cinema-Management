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

    public function dataTables ()
    {
       $malls = DB::select('SELECT * FROM malls'); 
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

        DB::insert('INSERT INTO malls (name, location, description, created_at, updated_at) VALUES (?, ?, ?, NOW(), NOW())', [
            $request->name,
            $request->location,
            $request->description,
        ]);

        return response()->json(['message' => 'Mall added successfully']);
    }
}

