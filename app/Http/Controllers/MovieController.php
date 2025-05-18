<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\DataTables;

class MovieController extends Controller
{
    public function list () {
        return view ('movies.list');
    }

    public function dataTables ()
    {
      $movies = DB::select('SELECT * FROM movies');

       return DataTables::of($movies)->make(true);
    }
}
