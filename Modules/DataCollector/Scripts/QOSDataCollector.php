<?php

namespace Modules\DataCollector\Scripts;
use Exception;
use Illuminate\Support\Facades\Http;
use Modules\Common\Entities\ExtractionLog;
use Modules\QOS\Entities\Inverter;
use Modules\QOS\Entities\Company;
use Modules\QOS\Entities\Measurement;
use Illuminate\Support\Facades\DB;
use Modules\QOS\Entities\Plant;
use Modules\QOS\Entities\Sensor;
use Modules\DataCollector\Http\Controllers\DataCollectorController;

class QOSDataCollector
{
    protected $client;
    protected $token;
    protected $log;

    public const QOS = 'https://eekixe.api.qantum.net';
    public const LOGIN = '/v2/user/login';
    public const SITES = '/v2/sites';
    public const INVERTERS = '/v2/inverters?filter[sites.id]=';
    public const PLANTS = '/v2/plants';
    public const SENSORS = '/v2/sensors?filter[sensors.referent]=Inverter_AC_Energy&filter[sites.id]=';
    public const MEASUREMENTS = '/v2/sensors/measurements?filter[measurements.datetime][range]=';
    public const MEASUREMENTS_FILTERS = '&filter[sensors.resolution]=15&filter[sensors.id]=';

    public function __construct() {

        $this->client = Http::withOptions(['verify' => false, 'timeout' => 20])
        ->withHeader('Content-Type', 'application/json')
        //->retry(1, 10000)
        ->withoutVerifying();

        $response = $this->client->post($this::QOS.$this::LOGIN, [
            'login' => env('MAIL_QOS'),
            'password' => env('PASSWORD_QOS'),
        ]);

        $this->token = json_decode($response->body())->token;
        $this->log = new ExtractionLog();
    }

    public function token($email, $password)
    {
        $response = $this->client->post($this::QOS.$this::LOGIN, [
            'login' => $email,
            'password' => $password,
        ]);

        return $response->body();
    }

    public function sites()
    {
        $url = $this::QOS.$this::SITES;

        $payload = [];

        $attempt = $this->log->attempt('Company', 'qos_company', 'qos_site_id', 'all');

        do{
            try {
                $availability = DataCollectorController::checkLimitAvailability('qos');
            } catch (Exception $e) {
                $result = [
                    'status' => 'Error',
                    'message' => $e->getMessage(),
                ];
                return $result;
            }
            
            try{
                $response = $this->client->withHeader('Authorization', 'Bearer ' . $this->token)
                ->get($url);
            } catch (Exception $e) {
                $this->log->errorOnRequest('Company', 'qos_company', 'qos_site_id', 'all', $attempt);
            }

            $data = json_decode($response, true);

            foreach($data['data'] as $item)
            {
                $payload[] = [
                    'name' => isset($item['attributes']['name']) ? $item['attributes']['name'] : null,
                    'latitude' => isset($item['attributes']['location']['latitude']) ? $item['attributes']['location']['latitude'] :null,
                    'longitude' => isset($item['attributes']['location']['longitude']) ? $item['attributes']['location']['longitude'] : null,
                    'timezone' => isset($item['attributes']['timeZone']) ? $item['attributes']['timeZone'] : null,
                    'address1' => isset($item['attributes']['address']['address1']) ? $item['attributes']['address']['address1'] : null,
                    'address2' => isset($item['attributes']['address']['address2']) ? $item['attributes']['address']['address2'] : null,
                    'zip_code' => isset($item['attributes']['address']['zipCode']) ? $item['attributes']['address']['zipCode'] : null,
                    'city' => isset($item['attributes']['address']['city']) ? $item['attributes']['address']['city'] : null,
                    'country' => isset($item['attributes']['address']['country']) ? $item['attributes']['address']['country'] : null,
                    'owner_first_name' => isset($item['attributes']['owner']['firstName']) ? $item['attributes']['owner']['firstName'] : null,
                    'owner_last_name' => isset($item['attributes']['owner']['lastName']) ? $item['attributes']['owner']['lastName'] : null,
                    'owner_email' => isset($item['attributes']['owner']['email']) ? $item['attributes']['owner']['email'] : null,
                    'fitter_first_name' => isset($item['attributes']['fitter']['firstName']) ? $item['attributes']['fitter']['firstName'] : null,
                    'fitter_last_name' => isset($item['attributes']['fitter']['lastName']) ? $item['attributes']['fitter']['lastName'] : null,
                    'fitter_email' => isset($item['attributes']['fitter']['email']) ? $item['attributes']['fitter']['email'] : null,
                    'manager_first_name' => isset($item['attributes']['manager']['firstName']) ? $item['attributes']['manager']['firstName'] : null,
                    'manager_last_name' => isset($item['attributes']['manager']['lastName']) ? $item['attributes']['manager']['lastName'] : null,
                    'manager_email' => isset($item['attributes']['manager']['email']) ? $item['attributes']['manager']['email'] : null,
                    'qos_site_id' => $item['id']
                ];
            }

            $url = $data['links']['next'] ?? null;

        }while(isset($url));

        foreach($payload as $company)
        {
            
            $this->log->createOrError(new Company(), 'Company', 'qos_company', 'qos_site_id', $company['qos_site_id'], $company, $attempt);

        }
    }

    public function plants()
    {
        $url = $this::QOS.$this::PLANTS;

        $payload = [];

        $attempt = $this->log->attempt('Plant', 'qos_plant', 'qos_plant_id', 'all');

        do{
            try {
                $availability = DataCollectorController::checkLimitAvailability('qos');
            } catch (Exception $e) {
                $result = [
                    'status' => 'Error',
                    'message' => $e->getMessage(),
                ];
                return $result;
            }

            try{
                $response = $this->client->withHeader('Authorization', 'Bearer ' . $this->token)
                ->get($url);
            } catch (Exception $e) {
                $this->log->errorOnRequest('Plant', 'qos_plant', 'qos_plant_id', 'all', $attempt);
            }

            $data = json_decode($response, true);

            foreach($data['data'] as $plant)
            {
                $siteURL = $plant['relationships']['site']['links']['related'] ?? null;
                preg_match('/https:\/\/eekixe.api.qantum.net\/v2\/sites\/(?P<id>\d+)/', $siteURL, $site);
                $payload[] = [
                    'qos_plant_id' => $plant['id'],
                    'name' => $plant['attributes']['name'],
                    'type' => $plant['attributes']['plantType'],
                    'peak_power' => $plant['attributes']['peakPower'],
                    'commissioning_date' => $plant['attributes']['commissioningDate'],
                    'computation_start_date' => $plant['attributes']['computationStartDate'],
                    'invoicing_start_date' => $plant['attributes']['invoicingStartDate']['date'] ?? null,
                    'invoicing_end_date' => $plant['attributes']['invoicingEndDate']['date'] ?? null,
                    'timeZone' => $plant['attributes']['timeZone'],
                    'latitude' => $plant['attributes']['location']['latitude'],
                    'longitud' => $plant['attributes']['location']['longitude'],
                    'company_id' => Company::where('qos_site_id', $site['id'])->first()->id ?? null
                ];
            }

            $url = $data['links']['next'] ?? null;

        }while(isset($url));

        foreach ($payload as $plant) {

            $this->log->createOrError(new Plant(), 'Plant', 'qos_plant', 'qos_plant_id', $plant['qos_plant_id'], $plant, $attempt);

        }    
    }

    public function inverters()
    {
        $base = $this::QOS.$this::INVERTERS;

        $sites = Company::get()->toArray();

        $payload = [];

        foreach($sites as $site)
        {
            $url = $base.$site['qos_site_id'];

            $attempt = $this->log->attempt('Inverter', 'qos_inverter', 'qos_inverter_id', 'all');

            do{
                try {
                    $availability = DataCollectorController::checkLimitAvailability('qos');
                } catch (Exception $e) {
                    $result = [
                        'status' => 'Error',
                        'message' => $e->getMessage(),
                    ];
                    return $result;
                }

                try{ 
                     $this->client = Http::withOptions(['verify' => false, 'timeout' => 5])
                    ->withoutVerifying();                    
                    $response = $this->client->withHeader('Authorization', 'Bearer ' . $this->token)
                    ->get($url);
                }catch(Exception $e)
                {
                    $this->log->errorOnRequest('Inverter', 'qos_inverter', 'qos_inverter_id', 'all', $attempt);
                }
                
                $data = json_decode($response, true);
                
                foreach($data['data'] as $item)
                {
                    $plantURL = $item['relationships']['plant']['links']['related'] ?? null;
                    preg_match('/https:\/\/eekixe.api.qantum.net\/v2\/plants\/(?P<id>\d+)/', $plantURL, $plant);

                    $payload[] =[
                        'qos_inverter_id' => $item['id'],
                        'name' => isset($item['attributes']['name']) ? $item['attributes']['name'] : null,
                        'group' => isset($item['attributes']['group']) ? $item['attributes']['group'] : null,
                        'serial' => isset($item['attributes']['serial']) ? $item['attributes']['serial'] : null,
                        'manufacturer' => isset($item['attributes']['manufacturer']) ? $item['attributes']['manufacturer'] : null,
                        'model' => isset($item['attributes']['model']) ? $item['attributes']['model'] : null,
                        'peak_power' => isset($item['attributes']['peakPower']) ? $item['attributes']['peakPower'] : null,
                        'plant_id' => Plant::where('qos_plant_id', $plant['id'])->first()->id ?? null
                    ];
                }

                $url = $data['links']['next'] ?? null;
                
            }while(isset($url));

        }

        foreach($payload as $inverter)
        {

            $this->log->createOrError(new Inverter(), 'Inverter', 'qos_inverter', 'qos_inverter_id', $inverter['qos_inverter_id'], $inverter, $attempt);

        }
    }

    public function updateInverters()
    {
        $base = $this::QOS.$this::INVERTERS;

        $sites = Company::get()->toArray();
        $invertersID = Inverter::all()->pluck('qos_inverter_id')->toArray();
        $payload = [];

        $attempt = $this->log->attempt('Inverter', 'qos_inverter', 'qos_inverter_id', 'all');

        foreach($sites as $site)
        {
            $url = $base.$site['qos_site_id'];

            do{
                try {
                    $availability = DataCollectorController::checkLimitAvailability('qos');
                } catch (Exception $e) {
                    $result = [
                        'status' => 'Error',
                        'message' => $e->getMessage(),
                    ];
                    return $result;
                }

                try{
                    $response = $this->client->withHeader('Authorization', 'Bearer ' . $this->token)
                    ->get($url);
                } catch (Exception $e) {

                    $this->log->errorOnRequest('Inverter', 'qos_inverter', 'qos_inverter_id', 'all', $attempt);

                }
                $data = json_decode($response, true);

                foreach($data['data'] as $item)
                {
                    $plantURL = $item['relationships']['plant']['links']['related'] ?? null;
                    preg_match('/https:\/\/eekixe.api.qantum.net\/v2\/plants\/(?P<id>\d+)/', $plantURL, $plant);
                    if(in_array($item['id'],$invertersID)){
                        continue;
                    }
                    $payload[] =[
                        'qos_inverter_id' => $item['id'],
                        'name' => isset($item['attributes']['name']) ? $item['attributes']['name'] : null,
                        'group' => isset($item['attributes']['group']) ? $item['attributes']['group'] : null,
                        'serial' => isset($item['attributes']['serial']) ? $item['attributes']['serial'] : null,
                        'manufacturer' => isset($item['attributes']['manufacturer']) ? $item['attributes']['manufacturer'] : null,
                        'model' => isset($item['attributes']['model']) ? $item['attributes']['model'] : null,
                        'peak_power' => isset($item['attributes']['peakPower']) ? $item['attributes']['peakPower'] : null,
                        'plant_id' => Plant::where('qos_plant_id', $plant['id'])->first()->id ?? null
                    ];
                }

                $url = $data['links']['next'] ?? null;

            }while(isset($url));

        }


        foreach($payload as $inverter)
        {

            $this->log->createOrError(new Inverter(), 'Inverter', 'qos_inverter', 'qos_inverter_id', $inverter['qos_inverter_id'], $inverter, $attempt);
            
        }
        
    }

    public function sensors()
    {
        $qosSitesID = Company::get()->pluck('qos_site_id')->toArray();

        $sitesID = implode(',' ,$qosSitesID);


        $attempt = $this->log->attempt('Sensor', 'qos_sensor', 'qos_sensor_id', 'all');

        $url = $this::QOS.$this::SENSORS.$sitesID;

        do{
            try {
                $availability = DataCollectorController::checkLimitAvailability('qos');
            } catch (Exception $e) {
                $result = [
                    'status' => 'Error',
                    'message' => $e->getMessage(),
                ];
                return $result;
            }

            try{
                $response = $this->client->withHeader('Authorization', 'Bearer ' . $this->token)
                ->get($url);
            } catch (Exception $e) {
                $this->log->errorOnRequest('Sensor', 'qos_sensor', 'qos_sensor_id', 'all', $attempt);
            }

            $data = json_decode($response, true);

            foreach($data['data'] as $item)
            {

                $inverterURL = $item['relationships']['parent']['links']['related'];
                preg_match('/https:\/\/eekixe.api.qantum.net\/v2\/inverters\/(?P<id>\d+)/', $inverterURL, $inverter);
                $payload[] = [
                    'qos_sensor_id' => $item['id'],
                    'name' => isset($item['attributes']['name']) ? $item['attributes']['name'] : null,
                    'description' => isset($item['attributes']['description']) ? $item['attributes']['description'] : null,
                    'formula' => isset($item['attributes']['formula']) ? $item['attributes']['formula'] : null,
                    'referent' => isset($item['attributes']['referent']) ? $item['attributes']['referent'] : null,
                    'sampling' => isset($item['attributes']['sampling']) ? $item['attributes']['sampling'] : null,
                    'day_aggregation' => isset($item['attributes']['dayAggregation']) ? $item['attributes']['dayAggregation'] : null,
                    'month_aggregation' => isset($item['attributes']['monthAggregation']) ? $item['attributes']['monthAggregation'] : null,
                    'unit' => isset($item['attributes']['unit']) ? $item['attributes']['unit'] : null,
                    'sensor_type' => isset($item['attributes']['sensorType']) ? $item['attributes']['sensorType'] : null,
                    'inverter_id' => Inverter::where('qos_inverter_id', $inverter['id'])->first()->id ?? null
                ];
            }

            $url = $data['links']['next'] ?? null;

        }while(isset($url));


        foreach($payload as $sensor)
        {

            $this->log->createOrError(new Sensor(), 'Sensor', 'qos_sensor', 'qos_sensor_id', $sensor['qos_sensor_id'], $sensor, $attempt);

        }
    }

    public function measurement(string $start, string $end, string $sensor_id = null)
    {
        ini_set('max_execution_time', 300);
        ini_set('memory_limit', '512M');
        $dates = implode(',', [$start, $end]);

        //$qosSensorsID = Sensor::get()->pluck('qos_sensor_id')->toArray();
        $sensorsID = $sensor_id;
        if(null == $sensorsID)
        {
            $qosSensorsID = DB::table('qos_sensor')
            ->select('qos_sensor.qos_sensor_id')
            ->join('qos_inverter', 'qos_inverter.id', '=', 'qos_sensor.inverter_id')
            ->join('qos_plant', 'qos_plant.id', '=', 'qos_inverter.plant_id')
            ->where('qos_plant.id', '=', 8)
            ->get()->pluck('qos_sensor_id')->toArray();
            $sensorsID = implode(',' ,$qosSensorsID);
        }
    
        $url = $this::QOS.$this::MEASUREMENTS.$dates.$this::MEASUREMENTS_FILTERS.$sensorsID;

        $payload = [];

        do{
            try {
                $availability = DataCollectorController::checkLimitAvailability('qos');
            } catch (Exception $e) {
                $result = [
                    'status' => 'Error',
                    'message' => $e->getMessage(),
                ];
                return $result;
            }

            try {
                $response = $this->client->withHeader('Authorization', 'Bearer ' . $this->token)
                ->get($url);
            } catch (Exception $e) {
                //var_dump($e->getMessage());
                $result = [
                    'status' => 'Error',
                    'message' => 'Error on request'
                ];
                return $result;
            }

            $data = json_decode($response, true);
            if(!isset($data['data'])) {
                break;
            }
            foreach($data['data'] as $item)
            {
                foreach($item['attributes']['measures'] as $measure){
                    
                    $collected_at = explode('/',$measure[0])[0];
                    $datetime = new \DateTime($collected_at);
                    $datetime->setTimezone(new \DateTimeZone('UTC'));
                    $formatted_collected_at = $datetime->format('Y-m-d H:i:s');
                    $payload[] = [
                        'sensor_id' => Sensor::where('qos_sensor_id', $item['id'])->first()->id ?? null,
                        'collected_at' => $formatted_collected_at,
                        'timezone' => $item['attributes']['timeZone'],
                        'value' => $measure[1] ?? 0.0
                    ];
                }
            }

            try {
                Measurement::insertOrIgnore($payload);
            } catch(Exception $e)
            {
                //var_dump($e->getMessage());
                $result = [
                    'status' => 'Error',
                    'message' => $e->getMessage() . ' | Error on insert into database'
                ];
                return $result;
            }

            $url = $data['links']['next'] ?? null;
        }while(isset($url));

        // foreach($payload as $measurement)
        // {
            // try{
                // Measurement::create($measurement);
            // }catch(Exception $e)
            // {
                // throw new Exception($e);
                // continue;
            // }
        // }

        $result = [
            'status' => 'Completed',
            'message' => 'Success'
        ];

        return $result;
    }

}
