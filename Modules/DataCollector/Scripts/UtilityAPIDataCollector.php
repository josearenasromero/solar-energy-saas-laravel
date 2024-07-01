<?php

namespace Modules\DataCollector\Scripts;
use Exception;
use Illuminate\Support\Facades\Http;
use Modules\Common\Entities\ExtractionLog;
use Modules\UtilityAPI\Entities\Authorization;
use Modules\UtilityAPI\Entities\UtilityCompany;
use Modules\UtilityAPI\Entities\Meter;
use Modules\UtilityAPI\Entities\UtilityMeasurement;

class UtilityAPIDataCollector
{
    protected $client;
    protected $log;

    public const BASE_UTILITYAPI = 'https://utilityapi.com';
    public const AUTHORIZATIONS = '/api/v2/authorizations';
    public const METERS = '/api/v2/meters';
    public const INTERVALS = '/api/v2/intervals';
    public const COMPANIES = [
            'ACE' => 'Atlantic City Electric',
            'AEPOHIO' => 'American Electric Power Ohio',
            'ALPower' => 'Alabama Power',
            'Ameren' => 'Ameren',
            'AppalachianPower' => 'Appalachian Power',
            'APS' => 'Arizona Public Service Company',
            'AustinEnergy' => 'Austin Energy',
            'BGE' => 'Baltimore Gas & Electric',
            'CEIC' => 'Cleveland Electric Illuminating Company',
            'ComEd' => 'Commonwealth Edison',
            'ConEd' => 'Consolidated Edison New York',
            'CONSUMERSENERGY' => 'Consumers Energy',
            'DEMO' => 'Demonstration Utility',
            'DEMOUTILITY' => 'Demo Utility',
            'DOMINION' => 'Dominion Energy',
            'Duke' => 'Duke Energy',
            'EVRSRC' => 'Eversource Energy',
            'FCU' => 'Fort Collins Utilities',
            'FPL' => 'Florida Power and Light Company',
            'GAPower' => 'Georgia Power',
            'HECO' => 'Hawaii Electric',
            'JCPL' => 'Jersey Central Power and Light',
            'LADWP' => 'Los Angeles Department of Water & Power',
            'LAKEFRONT' => 'Lakefront Utilities Inc.',
            'MonPower' => 'Mon Power',
            'NATGD' => 'National Grid',
            'NGNY' => 'National Grid New York',
            'NVE' => 'Nevada Energy',
            'NYSEG' => 'New York State Electric and Gas Corporation',
            'OhioEd' => 'Ohio Edison',
            'ORU' => 'Orange and Rockland Utilities',
            'PacPower' => 'Pacific Power Utilities',
            'PCE' => 'Peninsula Clean Energy',
            'PECO' => 'PECO Energy',
            'Penelec' => 'Penelec',
            'PEPCO' => 'Potomac Electric Power Company',
            'PG&E' => 'Pacific Gas and Electric',
            'PORTGE' => 'Portland General Electric',
            'PotomacEd' => 'Potomac Edison',
            'PPL' => 'Pennsylvania Power and Light',
            'PSEG' => 'Public Services Electric and Gas',
            'PSEGLI' => 'Public Services Electric and Gas - Long Island',
            'PSO' => 'Public Service Company of Oklahoma',
            'RMP' => 'Rocky Mountain Power',
            'SCE' => 'Southern California Edison',
            'SDG&E' => 'San Diego Gas and Electric',
            'SFPUC' => 'San Francisco Public Utilities Commission',
            'SMT' => 'Smart Meter Texas',
            'SMUD' => 'Sacramento Municipal Utility District',
            'SoCalGas' => 'Southern California Gas Company',
            'SRP' => 'Salt River Project',
            'SSMPUC' => 'PUC Distribution Inc.',
            'SVCE' => 'Silicon Valley Clean Energy',
            'TEP' => 'Tucson Electric Power',
            'WestPennPower' => 'West Penn Power',
            'XCEL' => 'Xcel Energy',
            'WELLAND'	=> 'Welland Hydro-Electric System Corp'
    ];

    public function __construct() {
        $this->client = Http::withHeaders([
            'Authorization' => 'Bearer ' . env('TOKEN_UTILITY')
        ])->withOptions(['verify' => false]);

        $this->log = new ExtractionLog();
    }

    public function authorization()
    {

        $url = $this::BASE_UTILITYAPI.$this::AUTHORIZATIONS;

        $attempt = $this->log->attempt('Authorization', 'utilityapi_authorization', 'utility_id', 'all');

        do{
            try{
                $response = $this->client->get($url);
            }catch(Exception $e)
            {

                $this->log->errorOnRequest('Authorization', 'utilityapi_authorization', 'utility_id', 'all', $attempt);
            }
            $data = json_decode($response, true);

            $payload = [];

            foreach($data['authorizations'] as $item)
            {
                /*$payload['companies'] []= [
                    'id' => $item['utility'],
                    'name' => $this::COMPANIES[$item['utility']]
                ];
                $payload['authorizations'] []=[
                    'id' => $item['uid'],
                    'email' => $item['user_email'],
                    'user_id' => $item['user_uid'],
                    'utilityapi_id' => $item['utility']
                ];*/

                $payload[] = [
                    'customer_email' => isset($item['customer_email']) ? $item['customer_email'] : null,
                    'customer_signature_full_name' => isset($item['customer_signature']['full_name']) ? $item['customer_signature']['full_name'] : null,
                    'nickname' => isset($item['nickname']) ? $item['nickname'] : null,
                    'utility_id' => isset($item['uid']) ? $item['uid'] : null,
                    'user_email' => isset($item['user_email']) ? $item['user_email'] : null,
                    'user_uid' => isset($item['user_uid']) ? $item['user_uid'] : null,
                    'utility' => isset($item['utility']) ? $item['utility'] : null,
                ];
            }

            $url = $data['next'];

        }while(isset($url));

        $authorizations = $payload;

        foreach($authorizations as $authorization)
        {

            $this->log->createOrError(new Authorization(), 'Authorization', 'utilityapi_authorization', 'utility_id', $authorization['utility_id'], $authorization, $attempt);

        }
    }

    public function meters()
    {

        $metersURL = $this::METERS;

        // $authorizationsID = Authorization::get()->pluck('utility_id')->toArray();
//
        // if(isset($authorizationsID))
        // {
            // $stringIDs = implode(',', $authorizationsID);
            // $metersURL = $this::METERS.'?authorizations='.$stringIDs;
        // }

        $url = $this::BASE_UTILITYAPI.$metersURL;

        $attempt = $this->log->attempt('Meter', 'utilityapi_meter', 'utilityapi_meter_id', 'all');

        do{
            try{
                $response = $this->client->get($url);
            }catch(Exception $e){

                $this->log->errorOnRequest('Meter', 'utilityapi_meter', 'utilityapi_meter_id', 'all', $attempt);

            }

            $payload = [];

            $data = json_decode($response, true);

            foreach($data['meters'] as $item)
            {
                $payload[] = [
                    'utilityapi_meter_id' => $item['uid'],
                    'service_class' => $item['base']['service_class'],
                    'service_tariff' => $item['base']['service_tariff'],
                    'service_address' => $item['base']['service_address'],
                    'service_identifier' => $item['base']['service_identifier'],
                    'meter_numbers' => implode(',',$item['base']['meter_numbers']),
                    'billing_account' => $item['base']['billing_account'],
                    'billing_address' => $item['base']['billing_address'],
                    'billing_contact' => $item['base']['billing_contact'],
                    'authorization_id' => Authorization::where('utility_id',($item['authorization_uid']))->first()->id,
                ];
            }

            foreach ($payload as $meter) {

                $this->log->createOrError(new Meter(), 'Meter', 'utilityapi_meter', 'utilityapi_meter_id', $meter['utilityapi_meter_id'], $meter, $attempt);

            }

            $url = $data['next'];

        }while(isset($url));


        return $payload;
    }

    public function updateMeters()
    {

        $metersURL = $this::METERS;

        $lastMeterID = Meter::query()->orderBy('utilityapi_meter_id','ASC')->get(['utilityapi_meter_id'])[0]['utilityapi_meter_id'];
        $url = $this::BASE_UTILITYAPI.$metersURL."?after=".$lastMeterID;

        $attempt = $this->log->attempt('Meter', 'utilityapi_meter', 'utilityapi_meter_id', 'all');

        do{
            try {
                $response = $this->client->get($url);
            } catch (Exception $e) {

                $this->log->errorOnRequest('Meter', 'utilityapi_meter', 'utilityapi_meter_id', 'all', $attempt);
                
            }

            $data = json_decode($response, true);

            $payload = [];

            foreach($data['meters'] as $item)
            {
                $payload[] = [
                    'utilityapi_meter_id' => $item['uid'],
                    'service_class' => $item['base']['service_class'],
                    'service_tariff' => $item['base']['service_tariff'],
                    'service_address' => $item['base']['service_address'],
                    'service_identifier' => $item['base']['service_identifier'],
                    'meter_numbers' => implode(',',$item['base']['meter_numbers']),
                    'billing_account' => $item['base']['billing_account'],
                    'billing_address' => $item['base']['billing_address'],
                    'billing_contact' => $item['base']['billing_contact'],
                    'authorization_id' => Authorization::where('utility_id',($item['authorization_uid']))->first()->id,
                ];
            }

            foreach ($payload as $meter) {

                $this->log->createOrError(new Meter(), 'Meter', 'utilityapi_meter', 'utilityapi_meter_id', $meter['utilityapi_meter_id'], $meter, $attempt);

            }

            $url = $data['next'];

        }while(isset($url));


        return $payload;
    }

    public function intervals(string $start, string $end, array $meters = null)
    {
        ini_set('max_execution_time', 500);
        ini_set('memory_limit', '512M');

        $metersID = [];
        if(null === $meters) {
            $metersID = Meter::get()->pluck('id', 'utilityapi_meter_id');
        } else {
            $metersID = Meter::whereIn('id', $meters)->get()->pluck('id', 'utilityapi_meter_id');
        }
        
        foreach($metersID as $key => $meterID){

            $intervalsURL = $this::INTERVALS.'?meters='.$key.'&start='.$start.'&end='.$end;
            $url = $this::BASE_UTILITYAPI.$intervalsURL;

            do{
                try{
                $response = $this->client->get($url);
                } catch(Exception $e) {
                    return [
                        'status' => 'error',
                        'message' => 'Error on request to UtilityAPI'
                    ];
                }
                $data = json_decode($response, true);

                $payload = [];
                if(isset($data['intervals'][0]['readings']))
                {   
                    foreach($data['intervals'][0]['readings'] as $reading)
                    {
                        $current_payload = [
                            'meter_id' => $meterID,
                            'utilityapi_interval_uid' => $data['intervals'][0]['uid'],
                            'start_at' => $reading['start'],
                            'end_at' => $reading['end'],
                            'kwh_value' => $reading['kwh'],
                            'datapoints' => json_encode($reading['datapoints']),
                        ];

                        $inserting = UtilityMeasurement::upsert($current_payload, ['meter_id', 'start_at', 'end_at'], ['kwh_value', 'datapoints']);
                        //var_dump($inserting);
                        //var_dump($reading['start'] . ' - ' . $reading['end']);

                        $payload[] = $current_payload;
                    }
                }

                if(count($payload) < 1) {
                    return [
                        'status' => 'error',
                        'message' => 'No data found'
                    ];
                }

                /*try {
                    UtilityMeasurement::insertOrIgnore($payload);
                } catch(Exception $e)
                {
                    return [
                        'status' => 'error',
                        'message' => $e->getMessage() . ' | Error on insert data to database'
                    ];
                }*/
                $url = $data['next'] ?? null;
            }while(isset($url));
        }

        return [
            'status' => 'Completed',
            'message' => 'Success'
        ];
    }
}
