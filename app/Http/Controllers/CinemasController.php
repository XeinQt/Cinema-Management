<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class CinemasController extends Controller
{
    public function list () {
        return view ('cinemas.list');
    }
}
