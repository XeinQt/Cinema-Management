<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\DataTables;

class CinemasController extends Controller
{
    public function list () {
        return view ('cinemas.list');
    }

    public function dataTables () {
      $cinemas = DB::select('SELECT * FROM cinemas');
       return DataTables::of($cinemas)->make(true);
    }
}
