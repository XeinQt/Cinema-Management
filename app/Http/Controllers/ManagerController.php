<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\DB;

use Illuminate\Http\Request;

class ManagerController extends Controller
{
    public function list () {
        $managers = DB::select('SELECT * FROM managers');
        return view('manager.list', [
            'managers' => $managers
        ]);
    }
}
