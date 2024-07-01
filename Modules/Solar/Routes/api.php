<?php

use Modules\Solar\Http\Controllers\MeterController;
use Modules\Solar\Http\Controllers\PlantController;
use Modules\Solar\Http\Controllers\ScheduleEnergyTimeController;
use Modules\Solar\Http\Controllers\ScheduleIncrementalEnergyController;
use Modules\Solar\Http\Controllers\UserController;
use Modules\Solar\Http\Controllers\CompanyController;
use Modules\Solar\Http\Controllers\InverterController;
use Modules\Solar\Http\Controllers\SensorController;
use Modules\Solar\Http\Controllers\MeasurementController;
use Illuminate\Support\Facades\Route;
use Modules\Solar\Http\Controllers\ScheduleController;
use Modules\Solar\Http\Controllers\ScheduleRateController;
use Modules\Solar\Http\Controllers\UtilityAPIMeasurementController;
use Modules\Solar\Http\Controllers\UtilityController;
use Modules\Solar\Http\Controllers\AuthorizationController;
use Modules\Solar\Http\Controllers\MeterInfoController;
use Modules\Solar\Http\Controllers\StatementController;
use Modules\Solar\Http\Controllers\ExtractionLogController;

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

Route::prefix('solar')/*->middleware('auth:api')*/->group( function () {
    Route::prefix('users')->group(function () {
        Route::get('/', [UserController::class, 'index']);
        Route::get('/{id}', [UserController::class, 'get']);
        Route::post('/create', [UserController::class, 'create']);
        Route::patch('/update', [UserController::class, 'update']);
        Route::delete('/delete', [UserController::class, 'delete']);
    });
    Route::prefix('companies')->group(function () {
        Route::get('/', [CompanyController::class, 'index']);
        Route::get('/{id}', [CompanyController::class, 'get']);
        Route::patch('/{id}', [CompanyController::class, 'update']);
    });
    Route::prefix('inverters')->group(function () {
        Route::get('/', [InverterController::class, 'index']);
        Route::get('/{id}', [InverterController::class, 'get']);
        Route::patch('/', [InverterController::class, 'massiveUpdate']);
        Route::patch('/{id}', [InverterController::class, 'update']);
    });
    Route::prefix('sensors')->group(function () {
        Route::get('/', [SensorController::class, 'index']);
        Route::get('/{id}', [SensorController::class, 'get']);
    });
    Route::prefix('measurements')->group(function () {
        Route::get('/', [MeasurementController::class, 'index']);
        Route::get('/{id}', [MeasurementController::class, 'get']);
    });
    Route::prefix('authorizations')->group(function () {
        Route::get('/', [AuthorizationController::class, 'index']);
        Route::get('/{id}', [AuthorizationController::class, 'get']);
    });
    Route::prefix('schedules')->group(function () {
        Route::get('/', [ScheduleController::class, 'index']);
        Route::get('/{id}', [ScheduleController::class, 'get']);
    });
    Route::prefix('utilities')->group(function () {
        Route::get('/', [UtilityController::class, 'index']);
        Route::get('/{id}', [UtilityController::class, 'get']);
    });
    Route::prefix('schedule-rates')->group(function () {
        Route::get('/', [ScheduleRateController::class, 'index']);
        Route::get('/{id}', [ScheduleRateController::class, 'get']);
    });
    Route::prefix('plants')->group(function () {
        Route::get('/', [PlantController::class, 'index']);
        Route::get('/{id}', [PlantController::class, 'get']);
        Route::patch('/{id}', [PlantController::class, 'update']);
    });
    Route::prefix('schedule-incremental-energy')->group(function () {
        Route::get('/', [ScheduleIncrementalEnergyController::class,'index']);
        Route::get('/{id}', [ScheduleIncrementalEnergyController::class,'get']);
    });
    Route::prefix('energy-time')->group(function () {
        Route::get('/', [ScheduleEnergyTimeController::class,'index']);
        Route::get('/{id}', [ScheduleEnergyTimeController::class,'get']);
    });
    Route::prefix('statements')->group(function () {
        Route::post('/report', [StatementController::class,'index']);
    });
    Route::prefix('meters')->group(function () {
        Route::get('/', [MeterController::class,'index']);
        Route::get('/{id}', [MeterController::class,'get']);
        Route::patch('/{id}', [MeterController::class,'update']);
        Route::patch('/remove/{id}', [MeterController::class, 'removeMeter']);
        Route::post('/info', [MeterInfoController::class,'index']);
        Route::post('/info-utility', [MeterInfoController::class, 'indexUtility']);
    });
    Route::prefix('utility-measurement')->group(function () {
        Route::get('/', [UtilityAPIMeasurementController::class,'index']);
        Route::get('/{id}', [UtilityAPIMeasurementController::class,'get']);
    });
    Route::prefix('extraction-log')->group(function () {
        Route::get('/', [ExtractionLogController::class,'index']);
        Route::get('/{id}', [ExtractionLogController::class,'get']);
    });
});
