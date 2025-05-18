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
        $malls = DB::select('SELECT * FROM malls');

        return view('malls.list', [
            'malls' => $malls
        ]);
    }

    public function dataTables ()
    {
       $malls = DB::select('SELECT * FROM malls'); 
       return DataTables::of($malls)->make(true);
    }
}

