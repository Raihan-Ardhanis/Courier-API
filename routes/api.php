<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\CourierController;
use Illuminate\Support\Facades\Route;

Route::post('login', [AuthController::class, 'login'])->middleware('throttle:5,1');

Route::get('couriers', [CourierController::class, 'index']);
Route::get('couriers/{courier}', [CourierController::class, 'show']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('logout', [AuthController::class, 'logout']);
    Route::post('couriers', [CourierController::class, 'store']);
    Route::put('couriers/{courier}', [CourierController::class, 'update']);
    Route::patch('couriers/{courier}', [CourierController::class, 'update']);
    Route::delete('couriers/{courier}', [CourierController::class, 'destroy']);
});
