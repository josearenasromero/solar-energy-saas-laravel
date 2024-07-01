<?php

namespace App\Console;

use App\Jobs\QOS\InverterJob;
use App\Jobs\QOS\MeasurementJob;
use App\Jobs\QOS\PlantJob;
use App\Jobs\QOS\SensorJob;
use App\Jobs\QOS\SiteJob;
use App\Jobs\Utility\AuthorizationJob;
use App\Jobs\Utility\MeterJob;
use App\Jobs\Utility\IntervalJob;
use App\Jobs\RateAcuity\UtilityJob;
use App\Jobs\RateAcuity\ScheduleJob;
use App\Jobs\RateAcuity\ScheduleRateJob;
use App\Jobs\RateAcuity\ScheduleEnergyTimeJob;
use App\Jobs\RateAcuity\ScheduleIncrementalEnergyJob;
use App\Jobs\RateAcuity\ScheduleServiceChargeJob;
use App\Jobs\RateAcuity\ScheduleDemandTimeJob;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;


class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        //withoutOverlapping()
        //sendOutputTo
        //appendOutputTo

        //UtilityAPI collectors
        $schedule->job(new AuthorizationJob)->everyFourHours()
            ->sendOutputTo(storage_path('logs/utility/authorization.log'), true);

        $schedule->job(new MeterJob)->everyFourHours()
            ->sendOutputTo(storage_path('logs/utility/meter.log'), true);

        $schedule->job(new IntervalJob)->hourly()
            ->sendOutputTo(storage_path('logs/utility/interval.log'), true);

        //RateAcuity collectors
        $schedule->job(new UtilityJob)->everyFourHours()
            ->sendOutputTo(storage_path('logs/rateacuity/utility.log'), true);

        $schedule->job(new ScheduleJob)->everyFourHours()
            ->sendOutputTo(storage_path('logs/rateacuity/schedule.log'), true);

        $schedule->job(new ScheduleRateJob)->everyFourHours()
            ->sendOutputTo(storage_path('logs/rateacuity/schedulerate.log'), true);

        $schedule->job(new ScheduleEnergyTimeJob)->everyFourHours()
            ->sendOutputTo(storage_path('logs/rateacuity/scheduleenergytime.log'), true);

        $schedule->job(new ScheduleIncrementalEnergyJob)->everyFourHours()
            ->sendOutputTo(storage_path('logs/rateacuity/scheduleincrementalenergy.log'), true);

        $schedule->job(new ScheduleServiceChargeJob)->everyFourHours()
            ->sendOutputTo(storage_path('logs/rateacuity/scheduleservicecharge.log'), true);

        $schedule->job(new ScheduleDemandTimeJob)->everyFourHours()
            ->sendOutputTo(storage_path('logs/rateacuity/scheduledemandtime.log'), true);

        // QOS collectors
        $schedule->job(new SiteJob)->everyFourHours()
        ->sendOutputTo(storage_path('logs/qos/site.log'), true);

        $schedule->job(new PlantJob)->everyFourHours()
        ->sendOutputTo(storage_path('logs/qos/plant.log'), true);

        $schedule->job(new InverterJob)->everyFourHours()
        ->sendOutputTo(storage_path('logs/qos/inverter.log'), true);   

        $schedule->job(new SensorJob)->everyFourHours()
        ->sendOutputTo(storage_path('logs/qos/sensor.log'), true);

        $schedule->job(new MeasurementJob)->everyFourHours()
        ->sendOutputTo(storage_path('logs/qos/measurement.log'), true);
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
