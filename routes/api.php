<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\MallController;
use App\Http\Controllers\Api\CinemaController;
use App\Http\Controllers\Api\ManagerController;
use App\Http\Controllers\Api\ScreeningController;
use App\Http\Controllers\Api\MovieController;
use App\Http\Controllers\Api\BookingController;
use App\Http\Controllers\Api\CustomerController;

// API Routes with prefix 'api/'
Route::prefix('v1')->group(function () {
    // Malls API Endpoints
    Route::apiResource('malls', MallController::class);

    // Cinemas API Endpoints
    Route::apiResource('cinemas', CinemaController::class);

    // Managers API Endpoints
    Route::apiResource('managers', ManagerController::class);

    // Screenings API Endpoints
    Route::apiResource('screenings', ScreeningController::class);

    // Movies API Endpoints
    Route::apiResource('movies', MovieController::class);

    // Bookings API Endpoints
    Route::apiResource('bookings', BookingController::class);

    // Customers API Endpoints
    Route::apiResource('customers', CustomerController::class);
}); 