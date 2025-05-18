<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\DataTables;

class ScreeningController extends Controller
{
    public function list() {
        return view ('screening.list');
    }
      public function dataTables ()
    {
        $screening = DB::select('select * from screening');
        return DataTables::of($screening)->make(true);
    }
}
