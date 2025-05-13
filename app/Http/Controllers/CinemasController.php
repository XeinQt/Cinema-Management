<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CinemasController extends Controller
{
    public function list () {
        $cinemas = DB::select('SELECT * FROM cinemas');
        return view ('cinemas.list' ,[
            'cinemas' => $cinemas
        ]);
    }
}
