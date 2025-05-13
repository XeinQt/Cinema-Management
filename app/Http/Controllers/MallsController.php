<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MallsController extends Controller
{
    public function list() 
    {
        $malls = DB::select('SELECT * FROM malls');

        return view('malls.list', [
            'malls' => $malls
        ]);
    }
}

