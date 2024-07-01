<?php

namespace App\Jobs\Utility;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Modules\DataCollector\Http\Controllers\DataCollectorController;
use Modules\DataCollector\Http\Requests\UtilityIntervalsRequest;

class IntervalJob implements ShouldQueue
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
        $request_data = ['customer_meter'=> 1, 'meter_id' => 7524, 'plant_id' => 4];
        $request_utility = new UtilityIntervalsRequest($request_data);
        $data_collector_controller = new DataCollectorController();
        
        $data_collector_controller->utilityIntervals($request_utility);
    }
}
