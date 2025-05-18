<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\DataTables;

class BookingController extends Controller
{
    public function list () {
        return view ('bookings.list');
    }
     public function dataTables () {
        
     $bookings = DB::select('SELECT * FROM booking');
       return DataTables::of($bookings)->make(true);
    }
}
