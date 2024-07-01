<?php

namespace Modules\DataCollector\Http\Controllers;

use Exception;
use Illuminate\Routing\Controller;
use Modules\DataCollector\Http\Requests\UtilityIntervalsRequest;
use Modules\DataCollector\Scripts\QOSDataCollector;
use Modules\DataCollector\Scripts\RateAcuityAPIDataCollector;
use Modules\DataCollector\Scripts\UtilityAPIDataCollector;
use Modules\DataCollector\Scripts\AEDataCollector;
use Modules\Common\Entities\ExtractionLog;
use Modules\Common\Entities\ApiLimit;
use Modules\QOS\Entities\Measurement as QOSMeasurement;
use Modules\Solar\Entities\MeterPlant;
use Modules\QOS\Entities\Sensor;
use Modules\UtilityAPI\Entities\UtilityMeasurement;
use Modules\Utility\Entities\Authorization;
use Modules\Utility\Entities\Company;
use Modules\UtilityAPI\Entities\Meters;

class DataCollectorController extends Controller
{

    protected $utilityAPIDataCollector;
    protected $qosDataCollector;
    protected $rateAcuityDataCollector;
    protected $alsoEnergyDataCollector;

    public function __construct() {
        $this->utilityAPIDataCollector = new UtilityAPIDataCollector();
        $this->qosDataCollector = new QOSDataCollector();
        $this->rateAcuityDataCollector = new RateAcuityAPIDataCollector();
        $this->alsoEnergyDataCollector = new AEDataCollector(); 
    }

    public static function checkLimitAvailability($api) {
        $limit = ApiLimit::where('api', $api)->first();
        if(null === $limit) {
            //shouldn't be null
            throw new Exception('Api limit not found');
        }

        $now = new \DateTime('now', new \DateTimeZone('UTC'));
        $minute_limit = $limit->minute_limit;
        $minute_count = $limit->minute_count;
        $minute_reset = new \DateTime($limit->minute_reset, new \DateTimeZone('UTC'));
        $minute_last = new \DateTime($limit->minute_last, new \DateTimeZone('UTC'));

        if ($minute_limit > 0) {
            if ($minute_count >= $minute_limit) {
                $diff = $now->diff($minute_reset)->format('%i');
                if ($diff < 1) {
                    throw new Exception('Minute limit reached');
                } else {
                    $minute_count = 1;
                    $minute_reset = $now;
                }
            } else {
                $minute_count++;
            }

            $minute_last = $now;

            $limit->minute_count = $minute_count;
            $limit->minute_reset = $minute_reset->format('Y-m-d H:i:s');
            $limit->minute_last = $minute_last->format('Y-m-d H:i:s');
            $limit->save();
        }

        //get if daily_count is greater than daily_limit
        $now = new \DateTime('now', new \DateTimeZone('UTC'));
        $daily_limit = $limit->daily_limit;
        $daily_count = $limit->daily_count;
        $daily_reset = new \DateTime($limit->daily_reset, new \DateTimeZone('UTC'));
        $daily_last = new \DateTime($limit->daily_last, new \DateTimeZone('UTC'));

        if ($daily_limit > 0) {
            if ($daily_count >= $daily_limit) {
                $diff = $now->diff($daily_reset)->format('%s');

                if ($diff < 86400) { // 86400 seconds in a day
                    throw new Exception('Daily limit reached');
                } else {
                    $daily_count = 1;
                    $daily_reset = new \DateTime('tomorrow', new \DateTimeZone('UTC'));
                }
            } else {
                $daily_count++;
            }

            $daily_last = $now;

            $limit->daily_count = $daily_count;
            $limit->daily_reset = $daily_reset->format('Y-m-d H:i:s');
            $limit->daily_last = $daily_last->format('Y-m-d H:i:s');
            $limit->save();
        }

        //get if monthly_count is greater than monthly_limit
        $now = new \DateTime('now', new \DateTimeZone('UTC'));
        $monthly_limit = $limit->monthly_limit;
        $monthly_count = $limit->monthly_count;
        $monthly_reset = new \DateTime($limit->monthly_reset, new \DateTimeZone('UTC'));
        $monthly_last = new \DateTime($limit->monthly_last, new \DateTimeZone('UTC'));

        if ($monthly_limit > 0) {
            if ($monthly_count >= $monthly_limit) {
                $diff = $now->diff($monthly_reset)->format('%s');

                if ($diff < 2592000) {
                    throw new Exception('Monthly limit reached');
                } else {
                    $monthly_count = 1;
                    $monthly_reset = new \DateTime('first day of next month', new \DateTimeZone('UTC'));
                }
            } else {
                $monthly_count++;
            }

            $monthly_last = $now;

            $limit->monthly_count = $monthly_count;
            $limit->monthly_reset = $monthly_reset->format('Y-m-d H:i:s');
            $limit->monthly_last = $monthly_last->format('Y-m-d H:i:s');
            $limit->save();
        }

        //get if yearly_count is greater than yearly_limit
        $now = new \DateTime('now', new \DateTimeZone('UTC'));
        $yearly_limit = $limit->yearly_limit;
        $yearly_count = $limit->yearly_count;
        $yearly_reset = new \DateTime($limit->yearly_reset, new \DateTimeZone('UTC'));
        $yearly_last = new \DateTime($limit->yearly_last, new \DateTimeZone('UTC'));

        if ($yearly_limit > 0) {
            if ($yearly_count >= $yearly_limit) {
                $diff = $now->diff($yearly_reset)->format('%s');

                if ($diff < 31536000) {
                    throw new Exception('Yearly limit reached');
                } else {
                    $yearly_count = 1;
                    $yearly_reset = new \DateTime('first day of January next year', new \DateTimeZone('UTC'));
                }
            } else {
                $yearly_count++;
            }

            $yearly_last = $now;

            $limit->yearly_count = $yearly_count;
            $limit->yearly_reset = $yearly_reset->format('Y-m-d H:i:s');
            $limit->yearly_last = $yearly_last->format('Y-m-d H:i:s');
            $limit->save();
        }

        return true;
    }

    public function utilityAuthorization()
    {
        $authorizations = $this->utilityAPIDataCollector->authorization();

        return json_encode('Completed');
    }

    public function utilityMeters()
    {
        $meters = $this->utilityAPIDataCollector->meters();

        return json_encode('Completed');
    }
    public function utilityUpdateMeters()
    {

        $meters = $this->utilityAPIDataCollector->updateMeters();

        return json_encode('Completed');
    }

    public function utilityIntervals(UtilityIntervalsRequest $request)
    {
        $lima_tz = new \DateTimeZone('America/Lima');

        //get parameter custom_interval and custom_start and custom_end
        $custom_interval = (int) $request->input('custom_interval');
        $custom_end = $request->input('custom_end');
        $custom_meter = (int) $request->input('meter_id', 0);
        $plant_id = (int) $request->input('plant_id');

        if(0 === $plant_id) {
            throw new Exception('Plant id is required'); //as for now, ideally get for each existing plant in meter_plant
        }

        //only obtain for meters associated to a plant
        $meters_id = MeterPlant::where('plant_id', '=', $plant_id)->get()->pluck('meter_id')->toArray();

        foreach($meters_id as $meter) {
            if(0 !== $custom_meter && $custom_meter !== $meter) continue;

            //find out from which month to start obtaining data
            $last_date = UtilityMeasurement::where('meter_id', $meter)->orderBy('end_at', 'desc')->first();
            if(null === $last_date) {
                $last_date = (object) ['end_at' => '2023-01-01 00:00:00'];
            }

            if(1 === $custom_interval && null !== $custom_end) {
                $last_date = (object) ['end_at' => $custom_end];
            }

            //get to know if data is at least older than 1 week
            $start = new \DateTime('now', $lima_tz);
            $start->modify('-1 week');
            $last_date_object = new \DateTime($last_date->end_at, $lima_tz);

            //if data is older than 1 week, start obtaining data from the last date
            if($last_date_object < $start) {
                //we start 1 day before the last date at 00:00:00 hours
                $new_start = new \DateTime($last_date->end_at, $lima_tz);
                $new_start->modify('-1 day');
                $new_start = $new_start->format('Y-m-d\T00:00:00-00:00');

                //get to know if the diff between $new_start and today is greater than a month
                $one_month_ago = new \DateTime('now', $lima_tz);
                $one_month_ago->modify('-3 weeks');
                $new_start_object = new \DateTime($new_start, $lima_tz);

                //if the diff between $new_start and today is greater than a month, start obtaining data from the last date plus 1 month
                $new_end = new \DateTime('now', $lima_tz);
                $new_end = $new_end->format('Y-m-d\TH:i:s-00:00');
                if($new_start_object < $one_month_ago) {
                    $new_end_object = clone $new_start_object;
                    $new_end_object->modify('+3 weeks');
                    $new_end = $new_end_object->format('Y-m-d\T00:00:00-00:00');
                }

                $param_start = $new_start;
                $param_end = $new_end;

                //now we want to iterate per day from param_start to param_end
                $period = $this->periodsByDay($param_start, $param_end);
                //now iterate per day based on $period
                foreach($period as $dt) {
                    $end_dt = clone $dt;
                    $end_dt->modify('+1 day');

                    $start = $dt->format('Y-m-d\T00:00:00-08:00');
                    $end = $end_dt->format('Y-m-d\T00:00:00-08:00');

                    /*$valid = [
                        '2023-01-18T00:00:00-00:00',
                        '2023-01-19T00:00:00-00:00',
                        '2023-01-20T00:00:00-00:00',
                        '2023-01-21T00:00:00-00:00',
                    ];

                    if(!in_array($end, $valid)) continue;*/

                    //verify if getting this was attemped before
                    $log = ExtractionLog::where([
                        ['entity_name', 'UtilityMeasurement'],
                        ['table_name', 'utilityapi_measurements'],
                        ['key_name', 'meter_id'],
                        ['start_extracted_date', $dt->format('Y-m-d H:i:s')],
                        ['end_extracted_date', $end_dt->format('Y-m-d H:i:s')],
                        ['key_value', $meter]
                    ])->orderBy('attempt', 'desc')->first();

                    if (0 === $custom_interval && isset($log->status) && 'completed' === strtolower($log->status)) {
                        continue;
                    }

                    //var_dump($start . " -  " . $end . " - " . $meter);
                    $intervals = $this->utilityAPIDataCollector->intervals($start, $end, [$meter]);

                    $attempts = isset($log->attempt) ? $log->attempt + 1 : 1;
                    $attempt_date = new \DateTime('now', $lima_tz);

                    ExtractionLog::create([
                        'entity_name' => 'UtilityMeasurement',
                        'attempt' => $attempts,
                        'status' => $intervals['status'],
                        'message' => $intervals['message'],
                        'start_extracted_date' => $dt->format('Y-m-d H:i:s'),
                        'end_extracted_date' => $end_dt->format('Y-m-d H:i:s'),
                        'attempt_date' => $attempt_date->format('Y-m-d H:i:s'),
                        'table_name' => 'utilityapi_measurements',
                        'key_name' => 'meter_id',
                        'key_value' => $meter
                    ]);
                }
            }
        }
        return json_encode('Completed');
    }

    public function qosSites()
    {
        // $email = 'service@coldwellsolar.com';
        // $password = 'Flash#CS2020!';
        // $data = $this->qosDataCollector->token($email, $password);

        $data = $this->qosDataCollector->sites();

        return json_encode('Completed');
    }

    public function qosInverters()
    {
        $data = $this->qosDataCollector->inverters();

        return json_encode('Completed');
    }
    public function qosUpdateInverters()
    {
        $data = $this->qosDataCollector->updateInverters();
        return json_encode('Completed');
    }

    public function qosSensors()
    {
        $data = $this->qosDataCollector->sensors();

        return json_encode('Completed');
    }

    public function qosMeasurements()
    {
        $lima_tz = new \DateTimeZone('America/Lima');

        $plants_id = MeterPlant::where('plant_id', '=', 8)->get()->pluck('plant_id')->toArray();

        $sensors = Sensor::whereHas('inverter.plant', function ($query) use ($plants_id) {
            $query->whereIn('id', $plants_id);
        })->pluck('qos_sensor_id')->toArray();

        foreach($sensors as $sensor)
        {
            //find out from which month to start obtaining data
            $current_sensor = Sensor::where('qos_sensor_id', $sensor)->first();

            if(null === $current_sensor) continue;

            $last_date = QOSMeasurement::where('sensor_id', $current_sensor->id)->orderBy('collected_at', 'desc')->first();

            if(null === $last_date) {
                $last_date = (object) ['collected_at' => '2023-01-01 00:00:00'];
            }

            //get to know if data is at least older than 1 week
            $start = new \DateTime('now', $lima_tz);
            $start->modify('-1 week');
            $last_date_object = new \DateTime($last_date->collected_at, $lima_tz);

            //if data is older than 1 week, start obtaining data from the last date
            if($last_date_object < $start) {
                //we start 1 day before the last date at 00:00:00 hours
                $new_start = new \DateTime($last_date->collected_at, $lima_tz);
                $new_start->modify('-1 day');
                $new_start = $new_start->format('Y-m-d\T00:00:00-00:00');

                //get to know if the diff between $new_start and today is greater than a month
                $one_month_ago = new \DateTime('now', $lima_tz);
                $one_month_ago->modify('-3 weeks');
                $new_start_object = new \DateTime($new_start, $lima_tz);

                //if the diff between $new_start and today is greater than a month, start obtaining data from the last date plus 1 month
                $new_end = new \DateTime('now', $lima_tz);
                $new_end = $new_end->format('Y-m-d\TH:i:s-00:00');
                if($new_start_object < $one_month_ago) {
                    $new_end_object = clone $new_start_object;
                    $new_end_object->modify('+3 weeks');
                    $new_end = $new_end_object->format('Y-m-d\T00:00:00-00:00');
                }

                $param_start = $new_start;
                $param_end = $new_end;

                //now we want to iterate per month from param_start to param_end
                $period = $this->periods($param_start, $param_end);
                //now iterate per month based on $period
                foreach($period as $dt) {
                    $end_dt = clone $dt;
                    $end_dt->modify('+1 month');

                    $start = $dt->format('Y-m-d') . 'T00:00:00-00:00';
                    $end = $end_dt->format('Y-m-d') . 'T00:00:00-00:00';

                    //verify if getting this was attemped before
                    $log = ExtractionLog::where([
                        ['entity_name', 'QOSMeasurements'],
                        ['table_name', 'qos_measurements'],
                        ['key_name', 'qos_sensor_id'],
                        ['start_extracted_date', $dt->format('Y-m-d H:i:s')],
                        ['end_extracted_date', $end_dt->format('Y-m-d H:i:s')],
                        ['key_value', $sensor]
                    ])->orderBy('attempt', 'desc')->first();

                    if(isset($log->status) && 'completed' === strtolower($log->status)){
                        continue;
                    }
    
                    $data = $this->qosDataCollector->measurement($start, $end, $sensor);
    
                    $attempts = isset($log->attempt) ? $log->attempt + 1 : 1;
                    $attempt_date = new \DateTime('now', new \DateTimeZone('UTC'));
    
                    ExtractionLog::create([
                        'entity_name' => 'QOSMeasurement',
                        'attempt' => $attempts,
                        'status' => $data['status'],
                        'message' => $data['message'],
                        'start_extracted_date' => $dt->format('Y-m-d H:i:s'),
                        'end_extracted_date' => $end_dt->format('Y-m-d H:i:s'),
                        'attempt_date' => $attempt_date->format('Y-m-d H:i:s'),
                        'table_name' => 'qos_measurements',
                        'key_name' => 'qos_sensor_id',
                        'key_value' => $sensor
                    ]);
                }
            }
        }

        return json_encode('Completed');
    }

    public function qosPlants()
    {
        $data = $this->qosDataCollector->plants();

        return json_encode('Completed');
    }

    public function rateAcuityUtilities()
    {
        $data = $this->rateAcuityDataCollector->utilities();

        return json_encode('Completed');
    }

    public function rateAcuitySchedule()
    {
        $data = $this->rateAcuityDataCollector->schedule();

        return json_encode('Completed');
    }

    public function rateAcuityScheduleRates()
    {
        $data = $this->rateAcuityDataCollector->scheduleRate();

        return json_encode('Completed');
    }

    public function rateAcuityScheduleEnergyTime()
    {
        $data = $this->rateAcuityDataCollector->schedule_energy_time();

        return json_encode('Completed');
    }

    public function rateAcuityScheduleIncrementalEnergy()
    {
        $data = $this->rateAcuityDataCollector->schedule_incremental_energy();

        return json_encode('Completed');
    }

    public function rateAcuityServiceCharge()
    {
        $data = $this->rateAcuityDataCollector->schedule_service_charge();

        return json_encode('Completed');
    }

    public function rateAcuityDemandTime()
    {
        $data = $this->rateAcuityDataCollector->schedule_demand_time();

        return json_encode('Completed');
    }

    public function periods($start, $end){
        $start = new \DateTime($start, new \DateTimeZone('UTC'));
        $interval = new \DateInterval('P1M');
        $end = new \DateTime($end, new \DateTimeZone('UTC'));
        $period = new \DatePeriod($start, $interval, $end);
        return $period;
    }

    public function periodsByDay($start, $end){
        $start = new \DateTime($start, new \DateTimeZone('UTC'));
        $interval = new \DateInterval('P1D');
        $end = new \DateTime($end, new \DateTimeZone('UTC'));
        $period = new \DatePeriod($start, $interval, $end);
        return $period;
    }

    public function alsoEnergyLogin(){

        $email = env('MAIL_AE');
        $password = env('PASSWORD_AE');

        $data = $this->alsoEnergyDataCollector->token($email, $password);
        return $data;
    }

    public function alsoEnergyAllSites(){
        $force = 1; //0 to read only ones with missing data
        $data = $this->alsoEnergyDataCollector->all_sites($force);
        return json_encode('Completed');
    }

    public function alsoEnergySites(){
        $data = $this->alsoEnergyDataCollector->sites();
        return json_encode('Completed');
    }

    public function alsoEnergySiteHardware(){
        $data = $this->alsoEnergyDataCollector->hardware();
        return json_encode('Completed');
    }

    public function alsoEnergyMeasurements(){
       
        $from = '2023-08-01T00:00:00';
        $to = '2023-09-01T00:00:00';
        // $bin_sizes = 'Bin15Min';
        // $tz = 'America/Los_Angeles';
        $data = $this->alsoEnergyDataCollector->measurement($from, $to);
        return json_encode('Completed');
    }
}
