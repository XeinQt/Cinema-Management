<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\MallController;
use App\Http\Controllers\Api\CinemaController;
use App\Http\Controllers\Api\ManagerController;
use App\Http\Controllers\Api\ScreeningController;
use App\Http\Controllers\Api\MovieController;
use App\Http\Controllers\Api\BookingController;
use App\Http\Controllers\api\CustomerController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\api\MallsController;
use App\Http\Controllers\api\MoviesController;

// Public routes
Route::post('/login', [AuthController::class, 'login']);

// Protected routes
Route::middleware('auth:sanctum')->group(function() {
    Route::prefix('v1')->group(function () {
        // Other resource routes
        Route::apiResource('malls', MallController::class);
        Route::apiResource('cinemas', CinemaController::class);
        Route::apiResource('managers', ManagerController::class);
        Route::apiResource('screenings', ScreeningController::class);
        Route::apiResource('movies', MovieController::class);
        Route::apiResource('bookings', BookingController::class);
    });
});


// Protected routes
Route::middleware('auth:sanctum')->group(function() {

    //customer
    Route::get('/customers', [CustomerController::class, 'list']);
    Route::get('/customers/{id}', [CustomerController::class, 'seacrhById']);
    Route::post('/customers/create', [CustomerController::class, 'create']);
    Route::post('/customers/update/{id}', [CustomerController::class, 'update']);
    Route::delete('/customers/delete/{id}', [CustomerController::class, 'delete']);
    Route::post('/customers/updateActive/{id}', [CustomerController::class, 'updateActive']);
    Route::post('/customers/updateInActive/{id}', [CustomerController::class, 'updateInActive']);
    Route::get('/customersactive', [CustomerController::class, 'active']);
    Route::get('/customersinactive', [CustomerController::class, 'inactive']);

    //malls
    Route::get('/malls', [MallsController::class, 'list']);
    Route::get('/malls/{id}', [MallsController::class, 'seacrhById']);
    Route::post('/malls/create', [MallsController::class, 'create']);
    Route::post('/malls/update/{id}', [MallsController::class, 'update']);
    Route::delete('/malls/delete/{id}', [MallsController::class, 'delete']);
    Route::post('/malls/updateActive/{id}', [MallsController::class, 'updateActive']);
    Route::post('/malls/updateInActive/{id}', [MallsController::class, 'updateInActive']);
    Route::get('/mallsactive', [MallsController::class, 'active']);
    Route::get('/mallsinactive', [MallsController::class, 'inactive']);

    //manager
    Route::get('/managers', [ManagerController::class, 'list']);
    Route::get('/managers/{id}', [ManagerController::class, 'seacrhById']);
    Route::post('/managers/create', [ManagerController::class, 'create']);
    Route::post('/managers/update/{id}', [ManagerController::class, 'update']);
    Route::delete('/managers/delete/{id}', [ManagerController::class, 'delete']);
    Route::post('/managers/updateActive/{id}', [ManagerController::class, 'updateActive']);
    Route::post('/managers/updateInActive/{id}', [ManagerController::class, 'updateInActive']);
    Route::get('/managersactive', [ManagerController::class, 'active']);
    Route::get('/managersinactive', [ManagerController::class, 'inactive']);
    
    //cinema
    Route::get('/cinemas', [CinemaController::class, 'list']);
    Route::get('/cinemas/{id}', [CinemaController::class, 'seacrhById']);
    Route::post('/cinemas/create', [CinemaController::class, 'create']);
    Route::post('/cinemas/update/{id}', [CinemaController::class, 'update']);
    Route::delete('/cinemas/delete/{id}', [CinemaController::class, 'delete']);
    Route::post('/cinemas/updateActive/{id}', [CinemaController::class, 'updateActive']);
    Route::post('/cinemas/updateInActive/{id}', [CinemaController::class, 'updateInActive']);
    Route::get('/cinemasactive', [CinemaController::class, 'active']);
    Route::get('/cinemasinactive', [CinemaController::class, 'inactive']);

     //movie
    Route::get('/movies', [MoviesController::class, 'list']);
    Route::get('/movies/{id}', [MoviesController::class, 'seacrhById']);
    Route::post('/movies/create', [MoviesController::class, 'create']);
    Route::post('/movies/update/{id}', [MoviesController::class, 'update']);
    Route::delete('/movies/delete/{id}', [MoviesController::class, 'delete']);
    Route::post('/movies/updateActive/{id}', [MoviesController::class, 'updateActive']);
    Route::post('/movies/updateInActive/{id}', [MoviesController::class, 'updateInActive']);
    Route::get('/moviesactive', [MoviesController::class, 'active']);
    Route::get('/moviesinactive', [MoviesController::class, 'inactive']);
    
    //Screening
    Route::get('/screenings', [ScreeningController::class, 'list']);
    Route::get('/screenings/{id}', [ScreeningController::class, 'seacrhById']);
    Route::post('/screenings/create', [ScreeningController::class, 'create']);
    Route::post('/screenings/update/{id}', [ScreeningController::class, 'update']);
    Route::delete('/screenings/delete/{id}', [ScreeningController::class, 'delete']);
    Route::post('/screenings/updateActive/{id}', [ScreeningController::class, 'updateActive']);
    Route::post('/screenings/updateInActive/{id}', [ScreeningController::class, 'updateInActive']);
    Route::get('/screeningsactive', [ScreeningController::class, 'active']);
    Route::get('/screeningsinactive', [ScreeningController::class, 'inactive']);

    //Booking
    Route::get('/booking', [BookingController::class, 'list']);
    Route::get('/booking/{id}', [BookingController::class, 'seacrhById']);
    Route::post('/booking/create', [BookingController::class, 'create']);
    Route::post('/booking/update/{id}', [BookingController::class, 'update']);
    Route::delete('/bookingdelete/{id}', [BookingController::class, 'delete']);
    Route::post('/booking/updateActive/{id}', [BookingController::class, 'updateActive']);
    Route::post('/booking/updateInActive/{id}', [BookingController::class, 'updateInActive']);
    Route::get('/bookingactive', [BookingController::class, 'active']);
    Route::get('/bookinginactive', [BookingController::class, 'inactive']);
   
    
});



