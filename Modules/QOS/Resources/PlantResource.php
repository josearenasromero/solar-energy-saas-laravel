<?php

namespace Modules\QOS\Resources;

use Modules\AlsoEnergy\App\Models\AESite;
use Modules\AlsoEnergy\Resources\AESiteResource;
use Modules\Common\Resources\GenericResource;
use Modules\RateAcuity\Resources\UtilityResource;
use Modules\UtilityAPI\Resources\AuthorizationResource;
use Modules\Solar\Resources\MeterPlantResource;

class PlantResource extends GenericResource
{
    private $detailed;
    public function __construct($resource, $detailed = true)
    {
        parent::__construct($resource);
        $this->detailed = $detailed;
    }

    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'type' => $this->type,
            'peak_power' => $this->peak_power,
            'commissioning_date' => $this->commissioning_date,
            'computation_start_date' => $this->computation_start_date,
            'timeZone' => $this->timeZone,
            'latitude' => $this->latitude,
            'longitud' => $this->longitud,
            'company' => (isset($this->company)) ? new CompanyResource($this->company, false) : null,
            'authorization' => (isset($this->authorization)) ? new AuthorizationResource($this->authorization, false) : null,
            'utility' => (isset($this->utility)) ? new UtilityResource($this->utility, true) : null,
            'meter_plant' => (isset($this->meter_plant)) ? MeterPlantResource::collection($this->meter_plant) : null,
            'status' => $this->status ?? null,
            'ae_site' =>(isset($this->aeSite) && $this->detailed)?(new AESiteResource($this->aeSite,false)):null,
        ];
    }

    public function with($request)
    {
        return parent::with($request);
    }
}
