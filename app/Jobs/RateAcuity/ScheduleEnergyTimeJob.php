<?php

namespace App\Jobs\RateAcuity;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Modules\DataCollector\Http\Controllers\DataCollectorController;

class ScheduleEnergyTimeJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    
    /**
     * Create a new job instance.
     */
    public function __construct()
    {
        
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $data_collector_controller = new DataCollectorController();
        
        $data_collector_controller->rateAcuityScheduleEnergyTime();
    }
}
