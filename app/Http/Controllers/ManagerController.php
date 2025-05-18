<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\DataTables;
use Illuminate\Http\Request;

class ManagerController extends Controller
{
    public function list () {
       
        return view('manager.list');
    }
     public function dataTables ()
    {
      $managers = DB::select('SELECT * FROM managers');
       return DataTables::of($managers)->make(true);
    }
}
