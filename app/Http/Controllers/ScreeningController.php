<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ScreeningController extends Controller
{
    public function list() {

        $screening = DB::select('select * from screening');

        return view ('screening.list', [
            'screenings' =>  $screening
        ]);
    }
}
