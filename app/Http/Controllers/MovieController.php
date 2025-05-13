<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MovieController extends Controller
{
    public function list () {
        $movies = DB::select('SELECT * FROM movies');

        return view ('movies.list' , [
            'movies' => $movies
        ]);
    }
}
