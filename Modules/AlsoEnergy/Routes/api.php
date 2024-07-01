<?php

use Illuminate\Support\Facades\Route;
use Modules\AlsoEnergy\App\Http\Controllers\AEHardwareController;
use Modules\AlsoEnergy\App\Http\Controllers\AEMeasurementController;
use Modules\AlsoEnergy\App\Http\Controllers\AESiteController;

/*
    |--------------------------------------------------------------------------
    | API Routes
    |--------------------------------------------------------------------------
    |
    | Here is where you can register API routes for your application. These
    | routes are loaded by the RouteServiceProvider within a group which
    | is assigned the "api" middleware group. Enjoy building your API!
    |
*/

// Route::middleware(['auth:sanctum'])->prefix('v1')->name('api.')->group(function () {
//     Route::get('alsoenergy', fn (Request $request) => $request->user())->name('alsoenergy');
// });


Route::prefix('also-energy')->group(function () {
    // Route::middleware('auth:api')->group(function () {    
        Route::prefix('sites')->group(function () {
            Route::get('/', [AESiteController::class, 'index']);
            Route::get('/{id}', [AESiteController::class, 'get']);
            Route::patch('/{id}', [AESiteController::class, 'update']);
        });
        Route::prefix('hardwares')->group(function () {
            Route::get('/', [AEHardwareController::class, 'index']);
            Route::get('/{id}', [AEHardwareController::class, 'get']);
        });
        Route::prefix('measurements')->group(function () {
            Route::get('/', [AEMeasurementController::class, 'index']);
            Route::get('/{id}', [AEMeasurementController::class, 'get']);
        });
    // });
});
