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
}

