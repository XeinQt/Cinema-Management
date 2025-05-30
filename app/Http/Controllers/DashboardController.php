<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        try {
            // Get total counts
            $totalBookings = DB::table('booking')->where('active', 1)->count();
            $totalCustomers = DB::table('customer')->where('active', 1)->count();
            
            // Get movie counts
            $activeMovies = DB::table('movies')->where('active', 1)->count();
            $inactiveMovies = DB::table('movies')->where('active', 0)->count();
            $totalMovies = $activeMovies + $inactiveMovies;
            
            $totalCinemas = DB::table('cinemas')->where('active', 1)->count();

            // Get today's bookings
            $todayBookings = DB::table('booking')
                ->where('active', 1)
                ->whereDate('created_at', Carbon::today())
                ->count();

            // Get upcoming screenings - Removed active check from screenings table
            $upcomingScreenings = DB::table('screenings as s')
                ->join('movies as m', 's.movie_id', '=', 'm.movie_id')
                ->join('cinemas as c', 's.cinema_id', '=', 'c.cinema_id')
                ->select('s.*', 'm.title as movie_title', 'c.name as cinema_name')
                ->where('m.active', 1) // Only active movies
                ->where('c.active', 1) // Only active cinemas
                ->where('s.screening_time', '>', Carbon::now())
                ->orderBy('s.screening_time')
                ->limit(5)
                ->get();

            // Get recent bookings
            $recentBookings = DB::table('booking as b')
                ->join('customer as cust', 'b.customer_id', '=', 'cust.customer_id')
                ->join('screenings as s', 'b.screening_id', '=', 's.screening_id')
                ->join('movies as m', 's.movie_id', '=', 'm.movie_id')
                ->select(
                    'b.*',
                    DB::raw("CONCAT(cust.first_name, ' ', cust.last_name) as customer_name"),
                    'm.title as movie_title'
                )
                ->where('b.active', 1)
                ->where('cust.active', 1)
                ->where('m.active', 1)
                ->orderBy('b.created_at', 'desc')
                ->limit(5)
                ->get();

            // Get booking statistics by status
            $bookingStats = DB::table('booking')
                ->where('active', 1)
                ->select('status', DB::raw('count(*) as total'))
                ->groupBy('status')
                ->get();

            // Get most booked movies
            $popularMovies = DB::table('booking as b')
                ->join('screenings as s', 'b.screening_id', '=', 's.screening_id')
                ->join('movies as m', 's.movie_id', '=', 'm.movie_id')
                ->select('m.title', DB::raw('count(*) as booking_count'))
                ->where('b.active', 1)
                ->where('m.active', 1)
                ->groupBy('m.movie_id', 'm.title')
                ->orderBy('booking_count', 'desc')
                ->limit(5)
                ->get();

            return view('dashboard', compact(
                'totalBookings',
                'totalCustomers',
                'activeMovies',
                'inactiveMovies',
                'totalMovies',
                'totalCinemas',
                'todayBookings',
                'upcomingScreenings',
                'recentBookings',
                'bookingStats',
                'popularMovies'
            ));

        } catch (\Exception $e) {
            \Log::error('Dashboard Error: ' . $e->getMessage());
            return view('dashboard')->with('error', 'Unable to load dashboard data. Please try again later.');
        }
    }
}