<?php

namespace Modules\DataCollector\Scripts;
use Exception;
use Http;
use Illuminate\Database\Eloquent\Model;
use Modules\Common\Entities\ExtractionLog;
use Modules\RateAcuity\Entities\ScheduleDemandTime;
use Modules\RateAcuity\Entities\ScheduleRate;
use Modules\RateAcuity\Entities\ScheduleServiceCharge;
use Modules\RateAcuity\Entities\Utility;
use Modules\RateAcuity\Entities\Schedule;
use Modules\RateAcuity\Entities\ScheduleEnergyTime;
use Modules\RateAcuity\Entities\ScheduleIncrementalEnergy;
use Modules\Solar\Entities\MeterPlant;

class RateAcuityAPIDataCollector
{
    protected $client;
    protected $log;

    public const BASE_RATE_ACUITY = 'https://secure.rateacuity.com/RateAcuityJSONAPI/api/';
    public const UTILITIES = 'utility';
    public const SCHEDULE = 'schedule/';
    public const SCHEDULE_RATE = 'scheduledetailtip/';
    public const AUTHORIZATION = [
        'p1' => 'service@coldwellbanker.com',
        'p2' => 'Power200'
    ];

    public function __construct()
    {
        $this->client = Http::withOptions(['verify' => false])->withoutVerifying();
        $this->log = new ExtractionLog();
    }

    public function utilities()
    {
        $url = self::BASE_RATE_ACUITY.self::UTILITIES;

        $attempt = $this->log->attempt('Utility', 'rateacuity_utility', 'utility_id', 'all');

        $payload = [];

        try{
            $response = $this->client->get($url, self::AUTHORIZATION);
        }catch(Exception $e){
            $this->log->errorOnRequest('Utility', 'rateacuity_utility', 'utility_id', 'all', $attempt);
        }

        $data = json_decode($response->body(), true);

        if(isset($data['Utility']))
        {
            foreach($data['Utility'] as $utility)
            {
                $payload[] = [
                    'utility_id' => $utility['UtilityID'],
                    'name' => $utility['UtilityName'],
                    'state' => $utility['State']
                ];
            }
        }

        foreach($payload as $utility)
        {
            $this->log->createOrError(new Utility(), 'Utility', 'rateacuity_utility', 'utility_id', $utility['utility_id'], $utility, $attempt);
        }

        return $payload;
    }

    public function schedule()
    {
        ini_set('max_execution_time', 0);
        $companiesID = Utility::get()->pluck('id','utility_id');

        $attempt = $this->log->attempt('Schedule', 'rateacuity_schedule', 'schedule_id', 'all');

        $i = 0;
        foreach($companiesID as $key => $utility){
            $payload = [];
            $url = self::BASE_RATE_ACUITY.self::SCHEDULE.$key;

            try {
                $response = $this->client->get($url, self::AUTHORIZATION);
            } catch (Exception $e) {
                $this->log->errorOnRequest('Schedule', 'rateacuity_schedule', 'schedule_id', 'all', $attempt);
            }

            $data = json_decode($response->body(), true);

            if(isset($data['Schedule']))
            {
                foreach($data['Schedule'] as $schedule)
                {
                    $payload[] = [
                        'pending' => $schedule['Pending'],
                        'schedule_id' => $schedule['ScheduleID'],
                        'utility_id' => $utility,
                        'schedule_name' => $schedule['ScheduleName'],
                        'schedule_description' => $schedule['ScheduleDescription'],
                        'use_type' => $schedule['UseType'],
                        'min_demand' => $schedule['MinDemand'],
                        'max_demand' => $schedule['MaxDemand'],
                        'min_usage' => $schedule['MinUsage'],
                        'max_usage' => $schedule['MaxUsage'],
                        'effective_date' => $schedule['EffectiveDate'],
                        'option_type' => $schedule['OptionType'],
                        'option_description' => $schedule['OptionDescription'],
                        'utility_name' => $schedule['UtilityName'],
                        'state' => $schedule['State'],
                    ];
                }
            }

            foreach ($payload as $schedule) {
                $this->log->createOrError(new Schedule(), 'Schedule', 'rateacuity_schedule', 'schedule_id', $schedule['schedule_id'], $schedule, $attempt);
            }
        }
    }

    public function scheduleRate()
    {
        ini_set('max_execution_time', 0);
        $schedulesID = Schedule::get()->pluck('schedule_id', 'id');

        $attempt = $this->log->attempt('ScheduleRate', 'rateacuity_schedulerate', 'schedule_rate', 'all');

        foreach($schedulesID as $key => $scheduleID)
        {
            $url = self::BASE_RATE_ACUITY.self::SCHEDULE_RATE.$scheduleID;

            try {
                $response = $this->client->get($url, self::AUTHORIZATION);
            } catch (Exception $e) {
                $this->log->errorOnRequest('ScheduleRate', 'rateacuity_schedulerate', 'schedule_rate', 'all', $attempt);
            }

            $data = json_decode($response->body(), true);

            $payload = [];

            if(isset($data[0]["Energy_Table"]))
            {
                foreach($data[0]["Energy_Table"] as $rate)
                {
                    $payload[] = [
                        'pending' => $rate['Pending'],
                        'schedule_id' => $key,
                        'real_schedule_id' => $rate['ScheduleID'],
                        'description' => $rate['Description'],
                        'rate_kwh' => $rate['RatekWh'],
                        'min_kv' => $rate['MinkV'],
                        'max_kv' => $rate['MaxkV'],
                        'determinant' => $rate['Determinant'],
                        'charge_unit' => $rate['ChargeUnit']
                    ];
                }

                foreach ($payload as $rate) {

                    $concat_key = implode(' - ', [$rate['ScheduleID'], $rate['Description'], $rate['ChargeUnit']]);

                    $this->log->createOrError(new ScheduleRate(), 'ScheduleRate', 'rateacuity_schedulerate', 'schedule_rate', $concat_key, $rate, $attempt);

                }
            }
        }
    }

    public function schedule_energy_time()
    {
        ini_set('max_execution_time', 0);

        //get all meter_plant so we can get each different schedule
        $meter_plant = MeterPlant::get()->pluck('schedule_id', 'id')->toArray();
        $schedule_energy_time = ScheduleEnergyTime::get()->pluck('schedule_id')->unique()->toArray();
        //get schedule_id from Schedule for each meter_plant
        $schedule_ids = [];
        foreach($meter_plant as $mp) {
            $current_schedule = Schedule::where('id', $mp)->first();
            if(null !== $current_schedule && !in_array($current_schedule->id, $schedule_energy_time)) {
                $schedule_ids[$mp] = $current_schedule->schedule_id;
            }
        }

        $attempt = $this->log->attempt('ScheduleEnergyTime', 'rateacuity_schedule_energytime', 'schedule_energy_time', 'all');

        foreach($schedule_ids as $key => $scheduleID)
        {
            $url = self::BASE_RATE_ACUITY.self::SCHEDULE_RATE.$scheduleID;
            try{
                $response = $this->client->get($url,self::AUTHORIZATION);
            } catch (Exception $e) {
                $this->log->errorOnRequest('ScheduleEnergyTime', 'rateacuity_schedule_energytime', 'schedule_energy_time', 'all', $attempt);
            }
            
            $data = json_decode($response->body(), true);

            $payload = [];
            if(isset($data[0]["EnergyTime_Table"]))
            {
                foreach($data[0]["EnergyTime_Table"] as $rate)
                {
                    $payload[] = [
                        'pending' => $rate['Pending'],
                        'schedule_id' => $key,
                        'description' => $rate['Description'],
                        'rate_kwh' => $rate['RatekWh'],
                        'min_kv' => $rate['MinkV'],
                        'max_kv' => $rate['MaxkV'],
                        'determinant' => $rate['Determinant'],
                        'season' => $rate['Season'],
                        'start_date' => $rate['StartDate'],
                        'end_date' => $rate['EndDate'],
                        'start_time' => $rate['StartTime'],
                        'end_time' => $rate['EndTime'],
                        'max_temp' => $rate['MaxTemp'],
                        'min_temp' => $rate['MinTemp'],
                        'charge_unit' => $rate['ChargeUnit'],
                        'time_of_day' => $rate['TimeOfDay'],
                        'day_app_desc' => $rate['DaysAppDesc'],
                    ];
                }

                foreach ($payload as $energy_time) {

                    $concat_key = implode(' - ', []); //TODO: add keys

                    $this->log->createOrError(new ScheduleEnergyTime(), 'ScheduleEnergyTime', 'rateacuity_schedule_energytime', 'schedule_energy_time', $concat_key, $energy_time, $attempt);

                }

            }
        }
    }

    public function schedule_incremental_energy()
    {
        ini_set('max_execution_time', 0);

        //get all meter_plant so we can get each different schedule
        $meter_plant = MeterPlant::get()->pluck('schedule_id', 'id')->toArray();
        $schedule_incremental_energy = ScheduleIncrementalEnergy::get()->pluck('schedule_id')->unique()->toArray();
        //get schedule_id from Schedule for each meter_plant
        $schedule_ids = [];
        foreach($meter_plant as $mp) {
            $current_schedule = Schedule::where('id', $mp)->first();
            if(null !== $current_schedule && !in_array($current_schedule->id, $schedule_incremental_energy)) {
                $schedule_ids[$mp] = $current_schedule->schedule_id;
            }
        }

        $attempt = $this->log->attempt('ScheduleIncrementalEnergy', 'rateacuity_schedule_incrementalenergy', 'schedule_incremental_energy', 'all');

        foreach($schedule_ids as $key => $scheduleID)
        {
            $url = self::BASE_RATE_ACUITY.self::SCHEDULE_RATE.$scheduleID;

            try{
                $response = $this->client->get($url,self::AUTHORIZATION);
            } catch (Exception $e) {
                $this->log->errorOnRequest('ScheduleIncrementalEnergy', 'rateacuity_schedule_incrementalenergy', 'schedule_incremental_energy', 'all', $attempt);
            }

            $data = json_decode($response->body(), true);

            $payload = [];
            if(isset($data[0]["IncrementalEnergy_Table"]))
            {
                foreach($data[0]["IncrementalEnergy_Table"] as $rate)
                {
                    $payload[] = [
                        'pending' => $rate['Pending'],
                        'schedule_id' => $key,
                        'description' => $rate['Description'],
                        'rate_kwh' => $rate['RatekWh'],
                        'start_kwh' => $rate['StartkWh'],
                        'end_kwh' => $rate['EndkWh'],
                        'determinant' => $rate['Determinant'],
                        'season' => $rate['Season'],
                        'start_date' => $rate['StartDate'],
                        'end_date' => $rate['EndDate'],
                        'start_time' => $rate['StartTime'],
                        'end_time' => $rate['EndTime'],
                        'max_temp' => $rate['MaxTemp'],
                        'min_temp' => $rate['MinTemp'],
                        'charge_unit' => $rate['ChargeUnit'],
                        'time_of_day' => $rate['TimeOfDay'],
                        'day_app_desc' => $rate['DaysAppDesc'],
                    ];
                }

                foreach ($payload as $incremental_energy) {

                    $concat_key = implode(' - ', []); //TODO: add keys

                    $this->log->createOrError(new ScheduleIncrementalEnergy(), 'ScheduleIncrementalEnergy', 'rateacuity_schedule_incrementalenergy', 'schedule_incremental_energy', $concat_key, $incremental_energy, $attempt);

                }
            }
        }
    }

    public function schedule_service_charge()
    {
        //get all meter_plant so we can get each different schedule
        $meter_plant = MeterPlant::get()->pluck('schedule_id', 'id')->toArray();
        $schedule_incremental_energy = ScheduleServiceCharge::get()->pluck('schedule_id')->unique()->toArray();
        //get schedule_id from Schedule for each meter_plant
        $schedule_ids = [];
        foreach($meter_plant as $mp) {
            $current_schedule = Schedule::where('id', $mp)->first();
            if(null !== $current_schedule && !in_array($current_schedule->id, $schedule_incremental_energy)) {
                $schedule_ids[$mp] = $current_schedule->schedule_id;
            }
        }

        $attempt = $this->log->attempt('ScheduleServiceCharge', 'rateacuity_schedule_servicecharge', 'schedule_service_charge', 'all');

        foreach($schedule_ids as $key => $scheduleID)
        {
            $url = self::BASE_RATE_ACUITY.self::SCHEDULE_RATE.$scheduleID;

            try{
                $response = $this->client->get($url,self::AUTHORIZATION);
            } catch (Exception $e) {
                $this->log->errorOnRequest('ScheduleServiceCharge', 'rateacuity_schedule_servicecharge', 'schedule_service_charge', 'all', $attempt);
            }

            $data = json_decode($response->body(), true);

            $payload = [];
            if(isset($data[0]["ServiceCharge_Table"]))
            {
                foreach($data[0]["ServiceCharge_Table"] as $rate)
                {
                    $payload[] = [
                        'schedule_id' => $key,
                        'description' => isset($rate['Description']) ? $rate['Description'] : null,
                        'rate' => isset($rate['Rate']) ? $rate['Rate'] : null,
                        'charge_unit' => isset($rate['ChargeUnit']) ? $rate['ChargeUnit'] : null,
                        'pending' => isset($rate['Pending']) ? $rate['Pending'] : null
                    ];
                }

                foreach ($payload as $service_charge) {

                    $concat_key = implode(' - ', []); //TODO: add keys

                    $this->log->createOrError(new ScheduleServiceCharge(), 'ScheduleServiceCharge', 'rateacuity_schedule_servicecharge', 'schedule_service_charge', $concat_key, $service_charge, $attempt);

                }
            }
            
        }
    }

    public function schedule_demand_time()
    {
        //get all meter_plant so we can get each different schedule
        $meter_plant = MeterPlant::get()->pluck('schedule_id', 'id')->toArray();
        $schedule_incremental_energy = ScheduleDemandTime::get()->pluck('schedule_id')->unique()->toArray();
        //get schedule_id from Schedule for each meter_plant
        $schedule_ids = [];
        foreach($meter_plant as $mp) {
            $current_schedule = Schedule::where('id', $mp)->first();
            if(null !== $current_schedule && !in_array($current_schedule->id, $schedule_incremental_energy)) {
                $schedule_ids[$mp] = $current_schedule->schedule_id;
            }
        }

        $attempt = $this->log->attempt('ScheduleDemandTime', 'rateacuity_schedule_demandtime', 'schedule_demand_time', 'all');

        foreach($schedule_ids as $key => $scheduleID)
        {
            $url = self::BASE_RATE_ACUITY.self::SCHEDULE_RATE.$scheduleID;

            try{
                $response = $this->client->get($url,self::AUTHORIZATION);
            } catch (Exception $e) {
                $this->log->errorOnRequest('ScheduleDemandTime', 'rateacuity_schedule_demandtime', 'schedule_demand_time', 'all', $attempt);
            }

            $data = json_decode($response->body(), true);

            $payload = [];
            if(isset($data[0]["DemandTime_Table"]))
            {
                foreach($data[0]["DemandTime_Table"] as $rate)
                {
                    $payload[] = [
                        'pending' => isset($rate['Pending']) ? $rate['Pending'] : null,
                        'schedule_id' => $key,
                        'description' => isset($rate['Description']) ? $rate['Description'] : null,
                        'rate_kw' => isset($rate['RatekW']) ? $rate['RatekW'] : null,
                        'min_kv' => isset($rate['MinkV']) ? $rate['MinkV'] : null,
                        'max_kv' => isset($rate['MaxkV']) ? $rate['MaxkV'] : null,
                        'season' => isset($rate['Season']) ? $rate['Season'] : null,
                        'start_date' => isset($rate['StartDate']) ? $rate['StartDate'] : null,
                        'end_date' => isset($rate['EndDate']) ? $rate['EndDate'] : null,
                        'time_of_day' => isset($rate['TimeOfDay']) ? $rate['TimeOfDay'] : null,
                        'start_time' => isset($rate['StartTime']) ? $rate['StartTime'] : null,
                        'end_time' => isset($rate['EndTime']) ? $rate['EndTime'] : null,
                        'min_temp' => isset($rate['MinTemp']) ? $rate['MinTemp'] : null,
                        'max_temp' => isset($rate['MaxTemp']) ? $rate['MaxTemp'] : null,
                        'day_app_desc' => isset($rate['DaysAppDesc']) ? $rate['DaysAppDesc'] : null,
                        'determinant' => isset($rate['Determinant']) ? $rate['Determinant'] : null,
                        'charge_unit' => isset($rate['ChargeUnit']) ? $rate['ChargeUnit'] : null,
                    ];
                }

                foreach ($payload as $demand_time) {

                    $concat_key = implode(' - ', []); //TODO: add keys

                    $this->log->createOrError(new ScheduleDemandTime(), 'ScheduleDemandTime', 'rateacuity_schedule_demandtime', 'schedule_demand_time', $concat_key, $demand_time, $attempt);

                }

            }
            
        }
    }
}
