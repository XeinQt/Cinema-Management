<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\DataTables;
use Illuminate\Http\Request;

class CustomerController extends Controller
{
    public function list() {

        return view('customer.list');
    }

      public function dataTables ()
    {
       $customer = DB::SELECT('SELECT * FROM customer');
       return DataTables::of($customer)->make(true);
    }
}
