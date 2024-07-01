<?php

namespace Modules\DataCollector\Scripts;

use Exception;
use Illuminate\Support\Facades\Http;
use Modules\AlsoEnergy\App\Models\AEHardware;
use Modules\AlsoEnergy\App\Models\AESite;
use Modules\Common\Entities\ExtractionLog;
use DateTime;
use Modules\AlsoEnergy\App\Models\AEMeasurement;

class AEDataCollector
{
    protected $client;
    protected $token;
    protected $log;

    public const ALSO_ENERGY = 'https://api.alsoenergy.com/';
    public const LOGIN = 'Auth/token';
    public const SITES = 'Sites';
    public const SITE_INFO = 'Sites/site_id';
    public const SITE_HARDWARE = 'Sites/site_id/Hardware';
    public const MEASUREMENTS = 'v2/Data/BinData';

    public const PATTERN = [
        'site_id' => '/site_id/',
    ];


    public function __construct()
    {
        $this->client = Http::withOptions(['verify' => false, 'timeout' => 20])
            ->withoutVerifying();

        $token = json_decode($this->token(env('MAIL_AE'), env('PASSWORD_AE')));

        if(!isset($token->access_token)) {
            throw new Exception('Could not get token');
        }

        $this->token = $token->access_token;
    
        $this->log = new ExtractionLog();
    }

    public function token($email, $password)
    {
        $response = $this->client->asForm()->post($this::ALSO_ENERGY . $this::LOGIN, [
            'grant_type' => 'password',
            'username' => $email,
            'password' => $password,
        ]);

        $response_json = json_decode($response);

        if(!isset($response_json->access_token)) {
            return $response->body();
        }
        
        $this->token = $response_json->access_token;

        return $response->body();
    }

    public function all_sites() {
        ini_set('max_execution_time', 0);
        ini_set('memory_limit', '512M');

        try{
            $this->client = Http::withOptions(['verify' => false, 'timeout' => 20])
            ->withoutVerifying();

            $token = json_decode($this->token(env('MAIL_AE'), env('PASSWORD_AE')));
            
            $this->token = $token->access_token;
            $response_site_info = $this->client->withHeader('Authorization', 'Bearer ' . $this->token)
            ->get($this::ALSO_ENERGY . 'Sites');

            $data = json_decode($response_site_info->body());

            if(200 === $response_site_info->status() && isset($data->items)) {
                $all_sites = $data->items;
                foreach($all_sites as $site){
                    $site_id = $site->siteId;
                    $site_name = $site->siteName;

                    $payload = [
                        'ae_site_id' => $site_id,
                        'name' => $site_name,
                    ];

                    AESite::upsert($payload, ['ae_site_id'], ['name']);
                }
            }

        }catch(Exception $e){

        }
    }

    public function sites(int $force = 0 ){

        ini_set('max_execution_time', 0);
        ini_set('memory_limit', '512M');

        //$force determines if we should read all or only ones with missing data
        if(0 === (int) $force) {
            $sites = AESite::whereNull('latitude')->get()->pluck('ae_site_id');
        } else {
            $sites = AESite::get()->pluck('ae_site_id');
        }
        $payload = [];
        
        foreach($sites as $site_id){

            $site_info = preg_replace($this::PATTERN['site_id'], $site_id, $this::SITE_INFO);

            try{
                $this->client = Http::withOptions(['verify' => false, 'timeout' => 20])
                ->withoutVerifying();

                $token = json_decode($this->token(env('MAIL_AE'), env('PASSWORD_AE')));
                $this->token = $token->access_token;
                $response_site_info = $this->client->withHeader('Authorization', 'Bearer ' . $this->token)
                ->get($this::ALSO_ENERGY . $site_info);

                $data = json_decode($response_site_info->body());

                if (200 !== $response_site_info->status())
                {
                    continue;
                }

                $turn_on_date = isset($data->turnOnDate) ? $turn_on_date = new DateTime($data->turnOnDate) : null;
               
                $timezone_name = isset($data->timeZone->gmtOffset) ? timezone_name_from_abbr("", $data->timeZone->gmtOffset * 3600, false) : null;
              
                $payload = [
                    'ae_site_id' => $data->siteId,
                    'name' => $data->name,
                    'latitude' => $data->location->latitude ?? null,
                    'longitude' => $data->location->longitude ?? null,
                    'address1' => $data->address->address1 ?? null,
                    'state' => $data->address->state ?? null,
                    'city' => $data->address->city ?? null,
                    'country' => $data->address->country ?? null,
                    'turn_on_date' => $turn_on_date->format('Y-m-d H:i:s'),
                    'zip_code' => $data->address->zip ?? null,
                    'timezone' => $timezone_name,
                ];

            }catch(Exception $e){
                continue;
            }
            // AESite::insert($payload);
            AESite::upsert($payload, ['ae_site_id'], ['name', 'latitude','longitude','address1','state','city','turn_on_date','zip_code','timezone']);
        }

        return 'OK';
    }

    public function hardware(){
        ini_set('max_execution_time', 0);
        ini_set('memory_limit', '512M');
        $sites = AESite::get()->pluck('id','ae_site_id');
        
        $payload = [];

        foreach($sites as $ae_site_id => $site_id){
            $site_hardware = preg_replace($this::PATTERN['site_id'], $ae_site_id, $this::SITE_HARDWARE);
           
            $this->client = Http::withOptions(['verify' => false, 'timeout' => 20])
                ->withoutVerifying();
            $token = json_decode($this->token(env('MAIL_AE'), env('PASSWORD_AE')));
            $this->token = $token->access_token;

            $response = $this->client
            ->withHeader('Authorization', 'Bearer ' . $this->token)         
            ->withQueryParameters([
                'includeArchivedFields' => 'true',
                'includeDeviceConfig' => 'true'
            ])
            ->get($this::ALSO_ENERGY . $site_hardware);
           
            $data = json_decode($response);
            
            if (isset($data->error))
            {
                continue;
            }

            if (isset($data)){
                foreach($data->hardware as $hardware){
                    $payload = [
                        'ae_site_id' => $site_id,
                        'ae_hardware_id' => $hardware->id,
                        'ae_hardware_str_id' => $hardware->stringId,
                        'name' => $hardware->config->name ?? null,
                        'device_type' => $hardware->config->deviceType ?? null,
                        'serial' => $hardware->config->serialNumber ?? null,
                        'field_name' => isset($hardware->fieldsArchived) ? json_encode($hardware->fieldsArchived) : null,
                        'rated_ac_power' => $hardware->config->inverterConfig->ratedAcPower ?? null,
                        'string_count' => $hardware->config->inverterConfig->stringCount ?? null,
                    ];
                    try{
                        // AEHardware::insert($payload);
                        AEHardware::upsert($payload, ['ae_site_id','ae_hardware_id','ae_hardware_str_id'], ['name', 'device_type','serial','field_name','rated_ac_power','string_count']);
                    }catch(Exception $e){
                        continue;
                    }
                }
            }
        }
        return 'OK';
    }

    public function measurement($from, $to, $bin_sizes = 'Bin15Min', $tz = 'America/Los_Angeles', $field_name_default = 'KWHnet', $function = 'Last'){
        ini_set('max_execution_time', 0);
        ini_set('memory_limit', '512M');
        $hardware = AEHardware::get();
        
        foreach($hardware as $item){
            try{
                $ae_site_id = $item->aeSites()->first()->ae_site_id;
                $ae_hardware_id = $item->ae_hardware_id; 
                $hardware_id = $item->id;              

                $field_name = isset($item->field_name) ? json_decode($item->field_name): [];
                
                if(!in_array($field_name_default, $field_name)){                    
                    continue;
                }
                $params = [[
                    "hardwareId"=> $ae_hardware_id,
                    "siteId"=> $ae_site_id,
                    "fieldName"=> $field_name_default,
                    "function"=> $function //Avg, Last, Min, Max, Diff, Sum, Integral, DiffNONZero
                ]];
               
                $this->client = Http::withOptions(['verify' => false, 'timeout' => 20])
                    ->withoutVerifying();
                $token = json_decode($this->token(env('MAIL_AE'), env('PASSWORD_AE')));
                $this->token = $token->access_token;
                
                $response = $this->client
                ->withHeader('Authorization', 'Bearer ' . $this->token)
                ->withBody(json_encode($params), 'application/json')          
                ->withQueryParameters([
                    'from' => $from,
                    'to' => $to,
                    'binSizes' => $bin_sizes, //BinUnknown, BinRaw, Bin5Min, Bin15Min, Bin1Hour, BinDay, BinMonth, BinYear
                    'tz' =>$tz
                ])->post($this::ALSO_ENERGY . $this::MEASUREMENTS);
                $data = json_decode($response);
                if (200 !== $response->status())
                {
                    continue;
                }
                if (isset($data)){
                    foreach($data->items as $item){
                        $collected_at = isset($item->timestamp) ? $collected_at = new DateTime($item->timestamp) : null;
                        
                        $payload = [
                            'ae_hardware_id' => $hardware_id,
                            'bin_size' => $bin_sizes,
                            'timezone' => $tz,
                            'collected_at' => $collected_at->format('Y-m-d H:i:s'),
                            'value' => $item->data[0] ?? null                          
                        ];
                      
                        AEMeasurement::upsert($payload, ['ae_hardware_id'], ['bin_size', 'timezone','collected_at','value']);
                       
                    }
                }
                
                
            }catch(Exception $e){
                continue;
            }
        }
    }

}
