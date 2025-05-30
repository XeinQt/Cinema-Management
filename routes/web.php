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
use App\Http\Controllers\DashboardController;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', [DashboardController::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::get('/MallsManagement', [MallsController::class, 'list'])->name('malls');
    Route::get('/MallsManagement/DataTables', [MallsController::class, 'dataTables'])->name('malls.dataTables');
    Route::post('/MallsManagement/create', [MallsController::class, 'store'])->name('malls.store');
    Route::post('/MallsManagement/updateStatus/{id}', [MallsController::class, 'updateStatus'])->name('malls.updateStatus');
    Route::post('/MallsManagement/update/{id}', [MallsController::class, 'update'])->name('malls.update');
    Route::post('/MallsManagement/restore/{id}', [MallsController::class, 'restore'])->name('malls.restore');

    Route::get('/CinemasManagement', [CinemasController::class, 'list'])->name('cinemas');
    Route::get('/CinemasManagement/DataTables', [CinemasController::class, 'dataTables'])->name('cinemas.dataTables');
    Route::post('/CinemasManagement/create', [CinemasController::class, 'store'])->name('cinemas.store');
    Route::post('/CinemasManagement/updateStatus/{id}', [CinemasController::class, 'updateStatus'])->name('cinemas.updateStatus');
    Route::post('/CinemasManagement/update/{id}', [CinemasController::class, 'update'])->name('cinemas.update');
    Route::post('/CinemasManagement/restore/{id}', [CinemasController::class, 'restore'])->name('cinemas.restore');

    Route::get('/ManagersManagement', [ManagerController::class, 'list'])->name('managers');
    Route::get('/ManagersManagement/DataTables', [ManagerController::class, 'dataTables'])->name('managers.dataTables');
    Route::post('/ManagersManagement/create', [ManagerController::class, 'store'])->name('managers.store');
    Route::post('/ManagersManagement/updateStatus/{id}', [ManagerController::class, 'updateStatus'])->name('managers.updateStatus');
    Route::post('/ManagersManagement/update/{id}', [ManagerController::class, 'update'])->name('managers.update');
    Route::post('/ManagersManagement/restore/{id}', [ManagerController::class, 'restore'])->name('managers.restore');

    Route::get('/MoviesManagement', [MovieController::class, 'list'])->name('movies');
    Route::get('/MoviesManagement/DataTables', [MovieController::class, 'dataTables'])->name('movies.dataTables');
    Route::post('/MoviesManagement/create', [MovieController::class, 'store'])->name('movies.store');
    Route::post('/MoviesManagement/updateStatus/{id}', [MovieController::class, 'updateStatus'])->name('movies.updateStatus');
    Route::post('/MoviesManagement/update/{id}', [MovieController::class, 'update'])->name('movies.update');
    Route::post('/MoviesManagement/restore/{id}', [MovieController::class, 'restore'])->name('movies.restore');

    Route::get('/ScreeningsManagement', [ScreeningController::class, 'list'])->name('screenings');
    Route::get('/ScreeningsManagement/DataTables', [ScreeningController::class, 'dataTables'])->name('screenings.dataTables');
    Route::post('/ScreeningsManagement/create', [ScreeningController::class, 'store'])->name('screenings.store');
    Route::post('/ScreeningsManagement/updateStatus/{id}', [ScreeningController::class, 'updateStatus'])->name('screenings.updateStatus');
    Route::post('/ScreeningsManagement/update/{id}', [ScreeningController::class, 'update'])->name('screenings.update');
    Route::post('/ScreeningsManagement/restore/{id}', [ScreeningController::class, 'restore'])->name('screenings.restore');

    Route::get('/BookingsManagement', [BookingController::class, 'list'])->name('bookings');
    Route::get('/BookingsManagement/DataTables', [BookingController::class, 'dataTables'])->name('bookings.dataTables');
    Route::post('/BookingsManagement/create', [BookingController::class, 'store'])->name('bookings.store');
    Route::post('/BookingsManagement/updateStatus/{id}', [BookingController::class,'updateStatus'])->name('bookings.updateStatus');
    Route::post('/BookingsManagement/update/{id}', [BookingController::class, 'update'])->name('bookings.update');
    Route::post('/BookingsManagement/restore/{id}', [BookingController::class, 'restore'])->name('bookings.restore');

    Route::get('/CustomersManagement', [CustomerController::class, 'list'])->name('customers');
    Route::get('/CustomersManagement/DataTables', [CustomerController::class, 'dataTables'])->name('customers.dataTables');
    Route::post('/CustomersManagement/create', [CustomerController::class, 'store'])->name('customers.store');
    Route::post('/CustomersManagement/updateStatus/{id}', [CustomerController::class, 'updateStatus'])->name('customers.updateStatus');
    Route::post('/CustomersManagement/update/{id}', [CustomerController::class, 'update'])->name('customers.update');
    Route::post('/CustomersManagement/restore/{id}', [CustomerController::class, 'restore'])->name('customers.restore');
});

require __DIR__.'/auth.php';