<?php

use Modules\QOS\Http\Controllers\QOSController;
use Illuminate\Support\Facades\Route;

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

Route::prefix('qos')->group(function () {
    Route::middleware('auth:api')->group(function () {
        Route::get('companies', [QOSController::class, 'list_companies']);
        Route::get('inverters', [QOSController::class, 'list_inverters']);
        Route::get('sensors', [QOSController::class, 'list_sensors']);
        Route::get('measurements', [QOSController::class, 'list_measurements']);
    });
});
