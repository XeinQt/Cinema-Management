<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BookingController extends Controller
{
    public function list () {
        $bookings = DB::select('SELECT * FROM booking');

        return view ('bookings.list', [
            'bookings' => $bookings
        ]);
    }
}
