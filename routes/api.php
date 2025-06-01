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

// Public routes
Route::post('/login', [AuthController::class, 'login']);

// Protected routes
Route::middleware('auth:sanctum')->group(function() {
    Route::prefix('v1')->group(function () {
        // Customer Routes
        Route::get('/customers', [CustomerController::class, 'list']);
        Route::get('/customers/{id}', [CustomerController::class, 'customerById']);
        Route::post('/customers/create', [CustomerController::class, 'create']);
        
        // Other resource routes
        Route::apiResource('malls', MallController::class);
        Route::apiResource('cinemas', CinemaController::class);
        Route::apiResource('managers', ManagerController::class);
        Route::apiResource('screenings', ScreeningController::class);
        Route::apiResource('movies', MovieController::class);
        Route::apiResource('bookings', BookingController::class);
    });
});

// Public routes
Route::post('/login', [AuthController::class, 'login']);

// Protected routes
Route::middleware('auth:sanctum')->group(function() {
    Route::get('/customers', [CustomerController::class, 'list']);
    Route::get('/customers/{id}', [CustomerController::class, 'customerById']);
    Route::post('/customers/create', [CustomerController::class, 'create']);
    Route::post('/customers/update/{id}', [CustomerController::class, 'update']);
    Route::delete('/customers/delete/{id}', [CustomerController::class, 'delete']);
});



