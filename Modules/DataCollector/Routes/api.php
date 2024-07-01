<?php

use Illuminate\Support\Facades\Route;
use Modules\DataCollector\Http\Controllers\DataCollectorController;

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

Route::prefix('collection')->group(function () {
    Route::prefix('utility')->group(function () {
        Route::get('authorization', [DataCollectorController::class, 'utilityAuthorization']);
        Route::get('meters', [DataCollectorController::class, 'utilityMeters']);
        Route::get('update-meters', [DataCollectorController::class, 'utilityUpdateMeters']);
        Route::get('intervals', [DataCollectorController::class, 'utilityIntervals']);
    });
    Route::prefix('qos')->group(function () {
        Route::get('sites', [DataCollectorController::class, 'qosSites']);
        Route::get('inverters', [DataCollectorController::class, 'qosInverters']);
        Route::get('update-inverters', [DataCollectorController::class, 'qosUpdateInverters']);
        Route::get('sensors', [DataCollectorController::class, 'qosSensors']);
        Route::get('measurement', [DataCollectorController::class, 'qosMeasurements']);
        Route::get('plants', [DataCollectorController::class, 'qosPlants']);
    });
    Route::prefix('rate-acuity')->group(function () {
        Route::get('utilities', [DataCollectorController::class, 'rateAcuityUtilities']);
        Route::get('schedule', [DataCollectorController::class, 'rateAcuitySchedule']);
        Route::get('schedule-rates', [DataCollectorController::class, 'rateAcuityScheduleRates']);
        Route::get('schedule-energy-time', [DataCollectorController::class, 'rateAcuityScheduleEnergyTime']);
        Route::get('schedule-incremental-energy', [DataCollectorController::class, 'rateAcuityScheduleIncrementalEnergy']);
        Route::get('schedule-service-charge', [DataCollectorController::class, 'rateAcuityServiceCharge']);
        Route::get('schedule-demand-time', [DataCollectorController::class, 'rateAcuityDemandTime']);
    });

    Route::prefix('also-energy')->group(function () {
        Route::get('login', [DataCollectorController::class, 'alsoEnergyLogin']);
        Route::prefix('sites')->group(function () {
            Route::get('/', [DataCollectorController::class, 'alsoEnergySites']);
            Route::get('/all', [DataCollectorController::class, 'alsoEnergyAllSites']);
            Route::prefix('hardware')->group(function () {
                Route::get('/', [DataCollectorController::class, 'alsoEnergySiteHardware']);
                Route::post('measurements', [DataCollectorController::class, 'alsoEnergyMeasurements']);
            });
        });
    });
});
