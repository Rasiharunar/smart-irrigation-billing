<?php

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group.
|
*/

use App\Http\Controllers\Api\UsageController;
use App\Http\Controllers\Api\PumpController;
use App\Http\Controllers\Api\SensorController;

// ESP8266 API Routes
Route::prefix('v1')->group(function () {
    Route::post('usage/start', [UsageController::class, 'start']);
    Route::post('usage/update', [UsageController::class, 'update']);
    Route::post('usage/stop', [UsageController::class, 'stop']);
    Route::get('pump/status/{id}', [PumpController::class, 'status']);
    Route::post('sensor/data', [SensorController::class, 'store']);
});