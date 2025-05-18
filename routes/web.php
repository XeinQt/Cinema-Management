<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\MallsController;
use App\Http\Controllers\CinemasController;
use App\Http\Controllers\ManagerController;
use App\Http\Controllers\ScreeningController;
use App\Http\Controllers\MovieController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\CustomerController;





Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');


    //mall controller
    Route::get('/MallsManagement', [MallsController::class, 'list'])->name('malls');
    //malls datatables
    Route::get('/MallsManagement/DataTables', [MallsController::class, 'dataTables'])->name('malls.dataTables');


    //cinemasController
    Route::get('/CinemasManagement', [CinemasController::class, 'list'])->name('cinemas');


    //managerController
    Route::get('/ManagersManagement', [ManagerController::class, 'list'])->name('managers');


    //Screnning Controller
    Route::get('/ScreeningsManagement', [ScreeningController::class, 'list'])->name('screenings');

    //Movies Controller
    Route::get('/MoviesManagement', [MovieController::class, 'list'])->name('movies');

    //Booking Controller
    Route::get('/BookingsManagement', [BookingController::class, 'list'])->name('bookings');

    //Customer Controller
    Route::get('/CustomersManagement', [CustomerController::class, 'list'])->name('customers');

    
});

require __DIR__.'/auth.php';
