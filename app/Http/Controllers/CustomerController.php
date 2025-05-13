<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\DB;

use Illuminate\Http\Request;

class CustomerController extends Controller
{
    public function list() {

        $customer = DB::SELECT('SELECT * FROM customer');

        return view('customer.list', [
            'customers' => $customer
        ]);
    }
}
