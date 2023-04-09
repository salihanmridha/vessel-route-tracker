<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::prefix("V1")->group(function () {
    Route::get(
        '/vessel/info',
        [\App\Http\Controllers\VesselController::class, 'vesselInfo']
    )->name('api.vessel.info');

    Route::get(
        '/vessel/route',
        [\App\Http\Controllers\VesselController::class, 'vesselRoute']
    )->name('api.vessel.route');

    Route::get(
        '/vessel/position',
        [\App\Http\Controllers\VesselController::class, 'vesselPosition']
    )->name('api.vessel.position');

    Route::get(
        '/vessel/mmsi-position',
        [\App\Http\Controllers\VesselController::class, 'vesselPositionByMmsi']
    )->name('api.vessel.position.mmsi');
});
